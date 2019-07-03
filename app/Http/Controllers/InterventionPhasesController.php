<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class InterventionPhasesController extends Controller
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
    public function index($intervention_id)
    {
        $phases = $this->service->getInterventionPhases($intervention_id);

        return response()->json(['data' => $phases]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayValidatorException
     */
    public function store(Request $request, int $intervention_id)
    {
        $validation = Validator::make($request->all(),
            array(
                'phases.*.phase_type_id' => 'required|integer|exists:phase_types,id',
                'phases.*.debut' => 'required|date_format:Y-m-d H:i'
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $phases = $this->service->addPhases($intervention_id, $validation->validated()['phases']);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $phases]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayValidatorException
     */
    public function update(Request $request, int $intervention_id)
    {
        $validation = Validator::make($request->all(),
            array(
                'phases.*.phase_type_id' => 'integer|exists:phase_type_id,id',
                'phases.*.debut' => 'date_format:Y-m-d H:i'
            )
        );

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $phases = $this->service->updatePhases($intervention_id, $validation->validated()['phases']);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $phases]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     */
    public function destroy(Request $request, int $intervention_id)
    {
        $validation = Validator::make($request->all(),
            array(
                'phases.*' => 'integer'
            )
        );
        //TODO Validation
        try {
            $this->service->removePhases($intervention_id, $validation->validated()['phases']);
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
