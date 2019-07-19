<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\API\ComptabiliteService;
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
        } catch (ArrayException $exception) {
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
        } catch (ArrayException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }
        return response()->json(['data' => $res]);
    }

    public function annuel(Request $request, int $id)
    {
        try {
            $res = $this->service->imputationAnnuel($id);
        } catch (ArrayException $exception) {
            return response()->json(['error' => $exception->getErrors()]);
        }
        return response()->json(['data' => $res]);
    }
}
