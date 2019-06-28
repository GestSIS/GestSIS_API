<?php

namespace App\Http\Controllers;

use App\Exceptions\ArrayValidatorException;
use App\Services\InterventionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class InterventionMissionsController extends Controller
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
        $missions = $this->service->getInterventionMissions($intervention_id);

        return response()->json(['data' => $missions]);
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
                'missions.*.sapeur_id' => 'integer|exists:sapeurs,id',
                'missions.*.debut' => 'required|date_format:Y-m-d H:i',
                'missions.*.fin' => 'required|date_format:Y-m-d H:i|after:missions.*.debut',
                'missions.*.titre' => 'string',
                'missions.*.resume' => 'string|nullable'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $missions = $this->service->addMissions($intervention_id, $request->get('missions'));
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $missions]);
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
                'missions.*.id' => 'integer|exists:missions,id',
                'missions.*.sapeur_id' => 'integer|exists:sapeurs,id',
                'missions.*.debut' => 'date_format:Y-m-d H:i',
                'missions.*.fin' => 'date_format:Y-m-d H:i|after:debut',
                'missions.*.titre' => 'string',
                'missions.*.resume' => 'string|nullable'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $missions = $this->service->updateMissions($intervention_id, $request->get('missions'));
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => $missions]);
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
                'missions.*' => 'integer|exists:missions,id'
            ));

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()]);
        }

        try {
            $this->service->removeMissions($intervention_id, $request->get('missions'));
        } catch (ArrayValidatorException $e) {
            return response()->json(['error' => $e->getErrors()]);
        }

        return response()->json(['data' => 'success']);
    }
}
