<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\Sapeur;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AlarmeService
{
    protected $repository;
    protected $business;

    public function __construct(InterventionRepository $repository, InterventionBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    /*
     * Retourne les alarmes disponibles avec les quittances liés aux sapeurs
     */
    public function listeAlarme($sisKey, $token, $force)
    {
        try {
            $response = Http::withHeaders([
                'Sis-Id' => $sisKey,
                'Authorization' => 'Bearer ' . $token
            ])
                ->acceptJson()
                ->timeout(3)
                ->get(config('gestsis.alarm_url', '') . '/api/v1/alarm', ['force_update' => $force]);

            if ($response->successful()) {
                $alarmes = $response->object();
            }

            // Enhance response -> Se base uniquement sur les sapeurs actifs
            $sapeurs = Sapeur::with('telephones')->where('actif', '=', 1)->get(['nom', 'prenom', 'suffixe', 'id']);

            $indexedSapeursByNomPrenom = [];
            foreach ($sapeurs as $sapeur) {
                // TODO: Manage homonymes
                $key = strtolower($sapeur['nom'] . " " . $sapeur['prenom']);
                $indexedSapeursByNomPrenom[$key] = $sapeur;
            }

            $alarmes = array_map(function ($alarme) use ($indexedSapeursByNomPrenom) {
                // Idée, simplement extraires les sapeurs sous leur forme que l'on peut rencontrer
                $tmp = [];
                $missing = [];

                // Method 1 en utilisant nom et prénom
                foreach ($alarme->firefighters as $f) {
                    $nomPrenom = strtolower($f->fullname);
                    if (array_key_exists($nomPrenom, $indexedSapeursByNomPrenom)) {
                        $f->id = $indexedSapeursByNomPrenom[$nomPrenom]->id;
                        array_push($tmp, $f);
                    } else {
                        array_push($missing, $f);
                    }
                }

                // Method 2 en utilisant les numéros de téléphones
                // TODO

                return $alarme;
            }, $alarmes);

            return $alarmes;
        } catch (Exception $e) {
            dd($e);
            throw new ArrayException([], "Une erreur est survenue lors de la récupération des alarmes");
        }
        // return $this->repository->listeIntervention($exerciceComptableId);
    }
}
