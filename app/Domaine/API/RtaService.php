<?php

namespace App\Domaine\API;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ReferenceRta;
use App\Infrastructure\Models\RtaParam;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RtaService
{
    protected $business;

    public function __construct(SapeurBusiness $business)
    {
        $this->business = $business;
    }

    public function getReferenceGestSis()
    {
        $sapeurs = Sapeur::where('actif', true)
            ->where('type', '=', SapeurBusiness::TYPE_SAPEUR)
            ->with([
                'groupes',
                'telephones' => function ($query) {
                    return $query->where('rta', true)->orderBy('priorite', 'ASC');
                }
            ])
            ->get(
                ['id', 'nom', 'prenom', 'fonction_id', 'localite_id', 'date_naissance', 'suffixe', DB::raw('CONCAT(rue," ",no_rue) as adresse')]
            )->toArray();

        $sapeurs = array_filter($sapeurs, function ($sapeur) {
            return count($sapeur['groupes']) > 0 && count($sapeur['telephones']);
        });

        return array_values(array_map(function ($sapeur) {
            $sapeur['groupes'] = array_map(function ($groupe) {
                return $groupe['groupe_id'];
            }, $sapeur['groupes']);
            return $sapeur;
        }, $sapeurs));
    }

    public function getReferenceRta()
    {
        $data = ReferenceRta::all()->toArray();

        return array_map(function ($s) {
            $data = json_decode($s['data'], true);
            unset($s['data']);
            $s = $s + $data;
            return $s;
        }, $data);

        // TODO: Reactivate once fully migrated
        $params = RtaParam::first();
        if (!$params) {
            throw new ArrayException(['message' => 'Paramètres RTA invalides'], 'Paramètres RTA invalides');
        }
        try {
            $bearerToken = Crypt::decryptString($params->token);
        } catch (DecryptException $e) {
            throw new ArrayException(['message' => 'Paramètres RTA invalides'], 'Paramètres RTA invalides');
        }

        $url = config("rta.api_url") . "/api/v2/demandes?limit=1";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        $demandeId = $response->json('data.0.id');

        $url = config("rta.api_url") . "/api/v2/demandes/$demandeId";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        return array_map(fn($s) => [
            ...$s,
            "sapeur_id" => intval($s['uuid']),
            "groupes" => array_map(fn($g) => ["no" => $g['numero']], $s['groupes']),
            "numeros" => array_map(fn($t) => $t['numero'], $s['moyens_contact']),
        ], $response->json('data.sapeurs'));
    }

    public function setReference($sapeurs)
    {
        $params = RtaParam::first();
        if (!$params) {
            throw new ArrayException(['message' => 'Paramètres RTA invalides'], 'Paramètres RTA invalides');
        }
        try {
            $bearerToken = Crypt::decryptString($params->token);
        } catch (DecryptException $e) {
            throw new ArrayException(['message' => 'Paramètres RTA invalides'], 'Paramètres RTA invalides');
        }

        if (count($sapeurs) <= 0) {
            throw new ArrayException(['sapeurs' => 'Aucun sapeur'], "Aucun sapeur présent dans la communication rta");
        }

        $url = config("rta.api_url") . "/api/v2/demandes";

        $data = array_map(function ($sapeur) {
            return [
                'uuid' => strval($sapeur['sapeur_id']),
                'nom' => $sapeur['nom'],
                'prenom' => $sapeur['prenom'],
                'date_naissance' => $sapeur['date_naissance'],
                'adresse' => $sapeur['adresse'],
                'suffixe' => $sapeur['suffixe'] ?? "",
                'localite' => $sapeur['localite'],
                'fonction' => $sapeur['fonction'] ?? "",
                'groupes' => array_map(fn($groupe) => ['numero' => $groupe['no']], $sapeur['groupes']),
                'moyens_contact' => array_map(
                    fn($tel, $index) => ['numero' => $tel, 'type' => 'Mobile', 'tri' => $index + 1],
                    $sapeur['numeros'],
                    array_keys($sapeur['numeros'])
                ),
            ];
        }, $sapeurs);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken,
            ])
                ->post($url, [
                    'sapeurs' => $data,
                    'soumettre' => true,
                    'message' => 'Mise à jour de la référence RTA depuis GestSIS',
                ]);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }

        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de l'envoi RTA");
        }

        return $this->getReferenceRta();
    }
}
