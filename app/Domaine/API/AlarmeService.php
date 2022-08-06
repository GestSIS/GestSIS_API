<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\InterventionRepository;
use Exception;
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
            ])->acceptJson()->timeout(3)->get(config('gestsis.alarm_url', '') . '/api/v1/alarm');

            if ($response->successful()) {
                $alarmes = $response->object();
            }

            // TODO: Enhance response

        } catch (Exception $e) {
            dd($e);
            throw new ArrayException([], "Une erreur est survenue lors de la récupération des alarmes");
        }
        // return $this->repository->listeIntervention($exerciceComptableId);
    }
}
