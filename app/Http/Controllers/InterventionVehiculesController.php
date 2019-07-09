<?php

namespace App\Http\Controllers;

use App\Business\InterventionBusiness;
use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionVehiculesController extends Controller
{

    protected $service;

    public function __construct(InterventionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($interventionId)
    {
        $vehicules = $this->service->getInterventionVehicules($interventionId);

        return response()->json(['data' => $vehicules]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $interventionId
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $interventionId)
    {
        $validation = Validator::make($request->all(),
        array(
            'vehicules.*' => 'required|integer'
        ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $vehicules = $this->service->addVehicules($interventionId, $request->get('vehicules'));
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $vehicules]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $interventionId
     * @return Response
     */
    public function destroy(Request $request, int $interventionId)
    {
        $validation = Validator::make($request->all(),
            array(
                'vehicules.*' => 'required|integer'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $this->service->removeVehicules($interventionId, $request->get('vehicules'));
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
