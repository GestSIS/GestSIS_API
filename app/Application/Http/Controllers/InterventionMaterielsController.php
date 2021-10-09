<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\InterventionMateriel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\Table;

class InterventionMaterielsController extends Controller
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
        $materiels = $this->service->getInterventionMateriels($intervention_id);

        return response()->json(['data' => $materiels]);
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
            'materiels.*.materiel_id' => 'required|exists:materiels,id',
            'materiels.*.quantite' => 'required|numeric|min:1'
        ]);

        $materiels = $this->service->addMateriels($intervention_id, $data['materiels']);

        return response()->json(['data' => $materiels]);
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
            'materiels.*.id' => 'required|exists:intervention_materiel,id',
            'materiels.*.quantite' => 'required|numeric|min:1'
        ]);

        $materiels = $this->service->updateMateriels($intervention_id, $data['materiels']);

        return response()->json(['data' => $materiels]);
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
            'materiels.*' => 'required|exists:intervention_materiel,id',
        ]);

        $this->service->removeMateriels($intervention_id, $data['materiels']);

        return response()->json(['data' => 'success']);
    }

    /**
     * Return le nombre d'intervention par materiel pour l'année comptable
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function stat(int $exercice_comptable_id)
    {
        $data = $this->service->statMateriel($exercice_comptable_id);

        return response()->json(['data' => $data]);
    }
}
