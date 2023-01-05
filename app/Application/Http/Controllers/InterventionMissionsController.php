<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @throws ArrayException
     */
    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'missions.*.sapeur_id' => 'nullable|integer|exists:sapeurs,id',
            'missions.*.sapeur' => 'nullable|string',
            'missions.*.debut' => 'required|date_format:Y-m-d H:i',
            'missions.*.fin' => 'required|date_format:Y-m-d H:i|after:missions.*.debut',
            'missions.*.titre' => 'string',
            'missions.*.resume' => 'string|nullable'
        ]);

        $missions = $this->service->addMissions($intervention_id, $data['missions']);

        return response()->json(['data' => $missions]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $intervention_id
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'missions.*.id' => 'integer|exists:missions,id',
            'missions.*.sapeur_id' => 'nullable|integer|exists:sapeurs,id',
            'missions.*.sapeur' => 'nullable|string',
            'missions.*.debut' => 'date_format:Y-m-d H:i',
            'missions.*.fin' => 'date_format:Y-m-d H:i|after:missions.*.debut',
            'missions.*.titre' => 'string',
            'missions.*.resume' => 'string|nullable'
        ]);

        $missions = $this->service->updateMissions($intervention_id, $data['missions']);

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
        $data = $request->validate([
            'missions.*' => 'integer|exists:missions,id'
        ]);

        $this->service->removeMissions($intervention_id, $data['missions']);

        return response()->json(['data' => 'success']);
    }
}
