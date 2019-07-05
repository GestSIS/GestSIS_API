<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\ComptabiliteService;

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
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

    public function intervention(int $interventionId)
    {
        try {
            $ecritures = $this->service->getEcrituresForInterventionById($interventionId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

    public function exercice(int $exerciceId)
    {
        try {
            $ecritures = $this->service->getEcrituresForExerciceById($exerciceId);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $ecritures]);
    }

}
