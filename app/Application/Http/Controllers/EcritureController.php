<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;

class EcritureController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    public function annuel(int $exerciceComptableId)
    {
        $ecritures = $this->service->getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);

        return response()->json(['data' => $ecritures]);
    }

    public function intervention(int $interventionId)
    {
        $ecritures = $this->service->getEcrituresForInterventionById($interventionId);

        return response()->json(['data' => $ecritures]);
    }

    public function exercice(int $exerciceId)
    {
        $ecritures = $this->service->getEcrituresForExerciceById($exerciceId);

        return response()->json(['data' => $ecritures]);
    }

}
