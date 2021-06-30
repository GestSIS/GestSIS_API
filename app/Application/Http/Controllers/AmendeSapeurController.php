<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ImputationService;

class AmendeSapeurController extends Controller
{
    protected $service;

    public function __construct(ImputationService $service)
    {
        $this->service = $service;
    }

    public function sapeur(int $exerciceComptableId, int $sapeurId)
    {
        $ecritures = $this->service->genererAmendesSapeur($exerciceComptableId, $sapeurId);

        return response()->json(['data' => $ecritures]);
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = $this->service->genererAmendeAnnuel($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }
}
