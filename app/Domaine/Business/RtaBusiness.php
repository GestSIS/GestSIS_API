<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\RtaParam;
use App\Models\Sapeur;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RtaBusiness
{
    public static function getReferenceGestSis()
    {
        return Sapeur::where('actif', true)
            ->where('type', SapeurBusiness::TYPE_SAPEUR)
            ->with([
                'groupes',
                'telephones' => function ($query) {
                    return $query->where('rta', true)->orderBy('priorite', 'ASC');
                }
            ])
            ->get(
                ['id', 'nom', 'prenom', 'fonction_id', 'localite_id', 'date_naissance', 'suffixe', DB::raw('CONCAT(rue," ",no_rue) as adresse')]
            )
            ->filter(fn($sapeur) => !empty($sapeur->groupes) && !empty($sapeur->telephones))
            ->map(function ($sapeur) {
                return array_merge($sapeur->toArray(), [
                    'groupes' => $sapeur->groupes->pluck('groupe_id')->toArray(),
                ]);
            })
            ->values()
            ->toArray();
    }

    public static function getDemandes()
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

        $url = config("rta.api_url") . "/api/v2/demandes";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer $bearerToken",
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        return $response->json();
    }

    public static function getFichiers()
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

        $url = config("rta.api_url") . "/api/v2/fichiers";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer $bearerToken",
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        return $response->json();
    }

    public static function downloadFichier(int $fichierId)
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

        $url = config("rta.api_url") . "/api/v2/fichiers/$fichierId";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/octet-stream',
                'Authorization' => "Bearer $bearerToken",
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        return [
            'content' => $response->body(),
            'content_type' => $response->header('Content-Type') ?? 'application/pdf',
            'filename' => $response->header('Content-Disposition')
                ? basename(explode('filename=', $response->header('Content-Disposition'))[1] ?? 'file.pdf')
                : "fichier-$fichierId.pdf"
        ];
    }

    public static function getReferenceRta()
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

        $url = config("rta.api_url") . "/api/v2/demandes?limit=1";
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer $bearerToken",
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
                'Authorization' => "Bearer $bearerToken",
            ])->get($url);
        } catch (\Exception $e) {
            throw new ArrayException(['message' => 'Erreur de communication avec RTA'], 'Erreur de communication avec RTA');
        }
        if ($response->failed()) {
            throw new ArrayException(["api_res" => $response->body()], "Erreur lors de la récupération RTA");
        }

        return collect($response->json('data.sapeurs'))
            ->map(fn($s) => [
                ...$s,
                "sapeur_id" => intval($s['uuid']),
                "groupes" => collect($s['groupes'])->map(fn($g) => ["no" => $g['numero']])->toArray(),
                "numeros" => $s['moyens_contact'],
            ])
            ->toArray();
    }

    public static function setReference($sapeurs)
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

        if (empty($sapeurs)) {
            throw new ArrayException(['sapeurs' => 'Aucun sapeur'], "Aucun sapeur présent dans la communication rta");
        }

        $url = config("rta.api_url") . "/api/v2/demandes";

        $data = collect($sapeurs)
            ->map(fn($sapeur) => [
                'uuid' => strval($sapeur['sapeur_id']),
                'nom' => $sapeur['nom'],
                'prenom' => $sapeur['prenom'],
                'date_naissance' => $sapeur['date_naissance'],
                'adresse' => $sapeur['adresse'],
                'suffixe' => $sapeur['suffixe'] ?? "",
                'localite' => $sapeur['localite'],
                'fonction' => $sapeur['fonction'] ?? "",
                'groupes' => collect($sapeur['groupes'])->map(fn($groupe) => ['numero' => strval($groupe['no'])])->toArray(),
                'moyens_contact' => $sapeur['numeros'],
            ])
            ->toArray();

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer $bearerToken",
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
            throw new ArrayException([], $response->json()['erreur'] ?? "Erreur lors de l'envoi RTA");
        }

        return static::getReferenceRta();
    }
}
