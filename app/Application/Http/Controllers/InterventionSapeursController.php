<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InterventionSapeursController extends Controller
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
        $sapeurs = $this->service->getInterventionPresences($interventionId);
        return response()->json(['data' => $sapeurs]);
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
            'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
            'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
            'sapeurs.*.piquet' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id'
        ]);

        $sapeurs = $this->service->addPresences($interventionId, $data['sapeurs']);

        return response()->json(['data' => $sapeurs]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $interventionId
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'sapeurs.*.id' => 'required|integer',
            'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
            'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
            'sapeurs.*.piquet' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer',
        ]);

        $sapeurs = $this->service->updatePresences($interventionId, $data['sapeurs']);

        return response()->json(['data' => $sapeurs]);
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
            'sapeurs.*' => 'required|integer'
        ]);

        $statut = $this->service->removePresences($interventionId, $data['sapeurs']);
        return response()->json(['data' => $statut]);
    }

    /**
     * Return les présences aux interventions pour l'année comptable
     *
     * @param Request $request
     * @param int $exerciceComptableId
     * @return Response
     */
    public function stat(int $exerciceComptableId)
    {
        $data = $this->service->statPresences($exerciceComptableId);

        return response()->json(['data' => $data]);
    }
}
