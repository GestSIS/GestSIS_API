<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;

class ImputationController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    public function exercice(Request $request, int $id)
    {
        try {
            $temp = $this->service->generateExercice($id, $request->all());
        } catch (ArrayValidatorException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }

        return response()->json(['data' => $temp]);
    }

    public function intervention(Request $request, int $id)
    {
        try {
            $temp = $this->service->generateIntervention($id, $request->all());
        } catch (ArrayValidatorException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }
        return response()->json(['data' => $temp]);
    }

    public function indemniteAnnuel()
    {

        return response()->json(['data' => 'TODO']);
    }

    public function fraisAnnuel()
    {


        return response()->json(['data' => 'TODO']);
    }
}
