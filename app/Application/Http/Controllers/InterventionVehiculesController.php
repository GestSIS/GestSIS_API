<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Intervention;
use App\Domaine\API\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

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
     * @throws ArrayException
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
        } catch (ArrayException $e) {
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
        } catch (ArrayException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
