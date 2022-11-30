<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurMutationController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $mutations = $this->service->getSapeurMutationsById($sapeurId);

        return response()->json(['data' => $mutations]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'incorporation' => 'required|date',
            'sortie' => 'date|nullable|after:incorporation',
            'motif' => 'string|nullable',
            'localite_id' => 'required|integer|exists:localites,id',
        ]);

        $mutation = $this->service->addMutation($sapeurId, $data);

        return response()->json(['data' => $mutation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $mutationId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $sapeurId, int $mutationId)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:mutations,id',
            'incorporation' => 'date',
            'sortie' => 'date|nullable|after:incorporation',
            'motif' => 'string|nullable',
            'localite_id' => 'integer|exists:localites,id',
        ]);

        if ($mutationId !== $request->get('id')) {
            return response()->json(['error' => 'invalid mutation id']);
        }

        $mutation = $this->service->updateMutation($sapeurId, $data);

        return response()->json(['data' => $mutation]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $mutationId
     * @return Response
     */
    public function destroy(int $sapeurId, int $mutationId)
    {
        $data = $this->service->removeMutation($sapeurId, $mutationId);

        return response()->json(['data' => $data]);
    }
}
