<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
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
        try {
            $ecritures = $this->service->getEcrituresAnnuelsForExerciceComptableById($exerciceComptableId);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

    public function intervention(int $interventionId)
    {
        try {
            $ecritures = $this->service->getEcrituresForInterventionById($interventionId);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

    public function exercice(int $exerciceId)
    {
        try {
            $ecritures = $this->service->getEcrituresForExerciceById($exerciceId);
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

}
