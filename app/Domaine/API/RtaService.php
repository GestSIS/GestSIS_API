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
    }

    public function resetReferenceRta()
    {
        ReferenceRta::truncate();
        return [];
    }

    public function setReference($data, $sis)
    {
        $sapeurs = array_key_exists('sapeurs', $data) ? $data['sapeurs'] : [];

        if (count($sapeurs) <= 0) {
            throw new ArrayException(['sapeurs' => 'Aucun sapeur'], "Aucun sapeur présent dans la communication rta");
        }

        $url = config("rta.api_url") . "/api/v2/demandes";

        // Envoie de la requête
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('rta.api_token'),
        ])
            ->post($url, $data);

        // TODO: Just check return code
        if (!str_contains($response->body(), '[TRS]OK')) {
            throw new ArrayException(["username" => "A vérifier", "password" => "A vérifier", "api_res" => $response->body()], "Identifiants incorrects");
        }

        return $this->getReferenceRta();
    }
}
