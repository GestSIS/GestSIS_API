<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionAppelsController extends Controller
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
        $appels = $this->service->getInterventionAppels($intervention_id);
        return response()->json(['data' => $appels]);
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
            'appels.*.date' => 'required|date_format:Y-m-d H:i',
            'appels.*.numero' => 'string',
            'appels.*.nom' => 'string',
            'appels.*.commentaire' => 'string|nullable'
        ]);

        $appels = $this->service->addAppels($intervention_id, $data['appels']);

        return response()->json(['data' => $appels]);
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
            'appels.*.id' => 'required|integer|exists:appels,id',
            'appels.*.date' => 'required|date_format:Y-m-d H:i',
            'appels.*.numero' => 'string',
            'appels.*.nom' => 'string',
            'appels.*.commentaire' => 'string|nullable'
        ]);

        $appels = $this->service->updateAppels($intervention_id, $data['appels']);

        return response()->json(['data' => $appels]);
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
            'appels.*' => 'integer'
        ]);

        $this->service->removeAppels($intervention_id, $data['appels']);

        return response()->json(['data' => 'success']);
    }
}
