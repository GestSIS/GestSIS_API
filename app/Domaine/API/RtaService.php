<?php

namespace App\Domaine\API;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ReferenceRta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class RtaService
{
    protected $business;

    public function __construct(SapeurBusiness $business)
    {
        $this->business = $business;
    }

    public function getReference()
    {
        return ReferenceRta::get();
    }

    public function setReference($data, $username, $password, $communication, $sis)
    {
        $date = Carbon::now();

        $ajoutes = array_key_exists('ajoutes', $data) ? $data['ajoutes'] : [];
        $modifies = array_key_exists('modifies', $data) ? $data['modifies'] : [];
        $supprimes = array_key_exists('supprimes', $data) ? $data['supprimes'] : [];

        // Controle que les sapeurs ne soient pas déjà présent
        $ajoutesId = array_map(function ($sapeur) {
            return $sapeur;
        }, $ajoutes);
        if (ReferenceRta::whereIn('sapeur_id', $ajoutesId)->count() > 0) {
            throw new ArrayException(['ajoutes' => 'Sapeur déjà présent'], "Ajout de sapeurs à double");
        }

        // Preparation des données pour fichier xml
        $ajoutes = array_map(function ($sapeur) {
            $sapeur['type'] = 1;
            return $sapeur;
        }, $ajoutes);
        $modifies = array_map(function ($sapeur) {
            $sapeur['type'] = 2;
            return $sapeur;
        }, $modifies);
        $supprimes = array_map(function ($sapeur) {
            $sapeur['type'] = 3;
            $sapeur['groupes'] = [];
            $sapeur['numeros'] = [];
            return $sapeur;
        }, $supprimes);

        $sapeurs = [
            ...$ajoutes,
            ...$modifies,
            ...$supprimes
        ];

        if (count($sapeurs) <= 0) {
            throw new ArrayException(['sapeurs' => 'Aucun sapeur'], "Aucun sapeur concerné");
        }

        // Génération du fichier xml pour RTA
        $xml = view('xml.rta', [
            'sis' => $sis,
            'date' => $date,
            'communication' => $communication,
            'sapeurs' => $sapeurs,
        ])->render();

        $url = "http://rta.gestsis.ch/gestionRtaJura/interfaceXML/transfert.php";

        //TODO: changer nom fichier
        $response = Http::attach(
            'fichier',
            $xml,
            'GestSIS-2.0-RTA-' . $sis . '-' . $date->format('d-m-Y_H:i') . '.xml'
        )
            ->withBasicAuth($username, $password)
            ->post($url);

        if (!str_contains($response->body(), '[TRS]OK')) {
            throw new ArrayException([], $response->body());
        }

        // Suppression, modification et ajout des sapeurs
        $supprimesId = array_map(function ($sapeur) {
            $sapeur['type'] = 3;
            return $sapeur;
        }, $supprimes);
        ReferenceRta::whereIn('sapeur_id', $supprimesId)->delete();

        $ajoutes = array_map(function ($sapeur) {
            $data = json_encode([
                'nom' => $sapeur['nom'],
                'prenom' => $sapeur['prenom'],
                'suffixe' => $sapeur['suffixe'],
                'localite' => $sapeur['localite'],
                'fonction' => $sapeur['fonction'],
                'date' => $sapeur['date'],
                'groupes' => $sapeur['groupes'],
                'numeros' => $sapeur['numeros'],
            ]);
            return [
                'sapeur_id' => $sapeur['id'],
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
                'date' => $sapeur['date'],
                'groupes' => $sapeur['groupes'],
                'numeros' => $sapeur['numeros'],
            ]);
            return [
                'sapeur_id' => $sapeur['id'],
                'data' => $data,
            ];
        }, $modifies);

        foreach ($modifies as $sapeur) {
            ReferenceRta::where('sapeur_id', $sapeur['sapeur_id'])->update($sapeur);
        }

        // Retourne la nouvelle référence
        return ReferenceRta::get();
    }
}
