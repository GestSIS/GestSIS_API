<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionGroupesController extends Controller
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
        $groupes = $this->service->getInterventionGroupes($interventionId);

        return response()->json(['data' => $groupes]);
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
        $data = $request->validate([
            'groupes.*.no' => 'required|string',
            'groupes.*.designation' => 'required|string',
        ]);

        $groupes = $this->service->addGroupes($interventionId, $data['groupes']);

        return response()->json(['data' => $groupes]);
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
        $data = $request->validate([
            'groupes.*' => 'required|integer'
        ]);

        $this->service->removeGroupes($interventionId, $data['groupes']);

        return response()->json(['data' => 'success']);
    }
}
