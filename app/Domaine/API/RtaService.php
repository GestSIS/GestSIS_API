<?php

namespace App\Domaine\API;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ReferenceRta;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
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
    }

    public function resetReferenceRta()
    {
        ReferenceRta::truncate();
        return [];
    }

    public function setReference($data, $username, $password, $communication, $sis)
    {
        $date = Carbon::now();
        if (is_null($communication) || $communication == '') {
            $communication = '-';
        }

        $ajoutes = array_key_exists('ajoutes', $data) ? $data['ajoutes'] : [];
        $modifies = array_key_exists('modifies', $data) ? $data['modifies'] : [];
        $supprimes = array_key_exists('supprimes', $data) ? $data['supprimes'] : [];

        // Controle que les sapeurs ne soient pas déjà présent
        $ajoutesId = array_map(function ($sapeur) {
            return $sapeur['sapeur_id'];
        }, $ajoutes);
        if (ReferenceRta::whereIn('sapeur_id', $ajoutesId)->count() > 0) {
            throw new ArrayException(['message' => 'Ajout d\'un sapeur déjà présent dans la référence RTA', 'ajoutes' => 'Sapeur déjà présent'], "Ajout de sapeurs à double");
        }

        // Preparation des données pour fichier xml
        $ajoutes = array_map(function ($sapeur) {
            $sapeur['type'] = 1;
            $sapeur['fonction'] = array_key_exists('fonction', $sapeur) ? $sapeur['fonction'] : '';
            return $sapeur;
        }, $ajoutes);
        $modifies = array_map(function ($sapeur) {
            $sapeur['type'] = 2;
            $sapeur['fonction'] = array_key_exists('fonction', $sapeur) ? $sapeur['fonction'] : '';
            return $sapeur;
        }, $modifies);
        $supprimes = array_map(function ($sapeur) {
            $sapeur['type'] = 3;
            $sapeur['groupes'] = [];
            $sapeur['numeros'] = [];
            $sapeur['fonction'] = array_key_exists('fonction', $sapeur) ? $sapeur['fonction'] : '';
            return $sapeur;
        }, $supprimes);

        $sapeurs = [
            ...$ajoutes,
            ...$modifies,
            ...$supprimes
        ];
        usort($sapeurs, fn ($a, $b) => strcmp($a['nom'] . $a['prenom'], $b['nom'] . $b['prenom']));

        if (count($sapeurs) <= 0) {
            throw new ArrayException(["message" => "Aucun sapeur dans la communication rta présente", 'sapeurs' => 'Aucun sapeur'], "Aucun sapeur concerné");
        }

        // Génération du fichier xml pour RTA
        $xml = view('xml.rta', [
            'sis' => $sis,
            'date' => $date,
            'communication' => $communication,
            'sapeurs' => $sapeurs,
        ])->render();

        $url = "https://gestionrta-jura.ch/gestionRtaJura/interfaceXML/transfert.php";

        // Envoie de la requête
        $response = Http::attach(
            'fichier',
            $xml,
            'GestSIS-2.0-RTA-' . $sis . '-' . $date->format('d-m-Y_H:i') . '.xml'
        )
            ->withBasicAuth($username, $password)
            ->post($url);

        if (!str_contains($response->body(), '[TRS]OK')) {
            throw new ArrayException(["username" => "A vérifier", "password" => "A vérifier", "message" => "Identifiants incorrects", "api_res" => $response->body()]);
        }

        // Suppression, modification et ajout des sapeurs
        $supprimesId = array_map(function ($sapeur) {
            return $sapeur['sapeur_id'];
        }, $supprimes);
        ReferenceRta::whereIn('sapeur_id', $supprimesId)->delete();

        $ajoutes = array_map(function ($sapeur) {
            $data = json_encode([
                'nom' => $sapeur['nom'],
                'prenom' => $sapeur['prenom'],
                'suffixe' => $sapeur['suffixe'],
                'localite' => $sapeur['localite'],
                'fonction' => $sapeur['fonction'],
                'date_naissance' => $sapeur['date_naissance'],
                'groupes' => $sapeur['groupes'],
                'numeros' => $sapeur['numeros'],
            ]);
            return [
                'sapeur_id' => $sapeur['sapeur_id'],
                'data' => $data,
            ];
        }, $ajoutes);
        ReferenceRta::insert($ajoutes);

        $modifies = array_map(function ($sapeur) {
            $data = json_encode([
                'nom' => $sapeur['nom'],
                'prenom' => $sapeur['prenom'],
                'suffixe' => $sapeur['suffixe'],
                'localite' => $sapeur['localite'],
                'fonction' => $sapeur['fonction'],
                'date_naissance' => $sapeur['date_naissance'],
                'groupes' => $sapeur['groupes'],
                'numeros' => $sapeur['numeros'],
            ]);
            return [
                'sapeur_id' => $sapeur['sapeur_id'],
                'data' => $data,
            ];
        }, $modifies);

        foreach ($modifies as $sapeur) {
            ReferenceRta::where('sapeur_id', $sapeur['sapeur_id'])->update($sapeur);
        }

        // Retourne la nouvelle référence
        return $this->getReferenceRta();
    }
}
