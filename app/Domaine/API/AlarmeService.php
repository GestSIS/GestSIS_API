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

            function cleanTelephoneFormat($phone)
            {
                $tmp = str_replace(" ", "", trim($phone));
                if (strlen($tmp) > 0 && substr($tmp, 0, 1) === "0") {
                    return "+41" . substr($tmp, 1);
                }
                return $tmp;
            };

            // Enhance response -> Se base uniquement sur les sapeurs actifs
            $sapeurs = Sapeur::with('telephones')->where('actif', '=', 1)->get(['nom', 'prenom', 'suffixe', 'id']);

            $indexedSapeursByNomPrenom = [];
            $indexedSapeursByPhone = [];
            foreach ($sapeurs as $sapeur) {
                $key = strtolower($sapeur['nom'] . " " . $sapeur['prenom']);
                $indexedSapeursByNomPrenom[$key] = $sapeur;
                foreach ($sapeur->telephones as $telephone) {
                    $numero = cleanTelephoneFormat($telephone->numero);

                    // Edge case lorsqu'un unique numéro est partagé par plusieurs sapeurs
                    if (array_key_exists($numero, $indexedSapeursByPhone) && $indexedSapeursByPhone[$numero] != $sapeur->id) {
                        $indexedSapeursByPhone[$numero] = null;
                    } else {
                        $indexedSapeursByPhone[$numero] = $sapeur->id;
                    }
                }
            }

            $alarmes = array_map(function ($alarme) use ($indexedSapeursByPhone, $indexedSapeursByNomPrenom) {
                // Idée, simplement extraires les sapeurs sous leur forme que l'on peut rencontrer
                $tmp = [];
                $missing = [];

                foreach ($alarme->firefighters as $f) {
                    // Method 1 en utilisant les numéros de téléphones
                    $phones = explode(",", $f->phone);
                    $resolved = false;
                    foreach ($phones as $phone) {
                        $numero = cleanTelephoneFormat($phone);
                        if ($numero !== "" && array_key_exists($numero, $indexedSapeursByPhone) && $indexedSapeursByPhone[$numero] !== null) {
                            $f->id = $indexedSapeursByPhone[$numero];
                            array_push($tmp, $f);
                            $resolved = true;
                            break;
                        }
                    }

                    if ($resolved) {
                        continue;
                    }

                    // Method 2 en utlisant le nom & prénom
                    $nomPrenom = strtolower($f->fullname);
                    if (array_key_exists($nomPrenom, $indexedSapeursByNomPrenom)) {
                        $f->id = $indexedSapeursByNomPrenom[$nomPrenom]->id;
                        array_push($tmp, $f);
                    } else {
                        array_push($missing, $f);
                    }
                }

                $alarme->firefighters = $tmp;

                if (count($missing) > 0) {
                    $alarme->unresolved = $missing;
                }

                return $alarme;
            }, $alarmes);

            return $alarmes;
        } catch (Exception $e) {
            dd($e);
            throw new ArrayException([], "Une erreur est survenue lors de la récupération des alarmes");
        }
    }
}
