<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\ComptabiliteService;
use Illuminate\Http\Request;
use Validator;

class ImputationController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    public function exercice(Request $request, int $id)
    {
        $validation = Validator::make($request->all(),
            array(
                'indemnite_exercice_type_id' => 'integer'
            )
        );

        try {
            $res = $this->service->imputationExercice($id, $validation->validated());
        } catch (ArrayValidatorException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }

        return response()->json(['data' => $res]);
    }

    public function intervention(Request $request, int $id)
    {
        $validation = Validator::make($request->all(),
            array(
                'indemnite_intervention_type_id' => 'integer'
            )
        );

        try {
            $res = $this->service->imputationIntervention($id, $validation->validated());
        } catch (ArrayValidatorException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }
        return response()->json(['data' => $res]);
    }

    public function annuel(Request $request, int $id)
    {
        try {
            $res = $this->service->imputationAnnuel($id);
        } catch (ArrayValidatorException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }
        return response()->json(['data' => $res]);
    }
}
