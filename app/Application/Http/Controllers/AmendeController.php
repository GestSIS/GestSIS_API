<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;

class AmendeController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    public function sapeur(int $exerciceComptableId, int $sapeurId)
    {
        $ecritures = $this->service->genererAmendeSapeur($exerciceComptableId, $sapeurId);

        return response()->json(['data' => $ecritures]);
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = $this->service->genererAmendeAnnuel($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }
}
