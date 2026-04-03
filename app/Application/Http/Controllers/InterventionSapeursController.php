<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\InterventionSapeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InterventionSapeursController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($interventionId)
    {
        return response()->json(['data' => InterventionSapeur::where('intervention_id', $interventionId)->get()]);
    }

    public function store(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'sapeurs' => 'required|array',
            'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
            'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
            'sapeurs.*.piquet' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id'
        ]);

        $statut = $this->business->addPresences($interventionId, $data['sapeurs']);
        return response()->json(['data' => [
            "statut" => $statut,
            "sapeurs" => InterventionSapeur::where('intervention_id', $interventionId)->get(),
        ]]);
    }

    public function update(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'sapeurs' => 'required|array',
            'sapeurs.*.id' => 'required|integer',
            'sapeurs.*.debut' => 'required|date_format:Y-m-d H:i',
            'sapeurs.*.fin' => 'required|date_format:Y-m-d H:i|after:sapeurs.*.debut',
            'sapeurs.*.piquet' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer',
        ]);

        $this->business->updatePresences($interventionId, $data['sapeurs']);
        return response()->json(['data' => InterventionSapeur::where('intervention_id', $interventionId)->get()]);
    }

    public function destroy(Request $request, int $interventionId)
    {
        $data = $request->validate([
            'sapeurs' => 'required|array',
            'sapeurs.*' => 'required|integer'
        ]);

        $statut = $this->business->removePresences($interventionId, $data['sapeurs']);
        return response()->json(['data' => $statut]);
    }

    public function stat(int $exerciceComptableId)
    {
        $data = DB::select("SELECT ins.*
                FROM intervention_sapeur as ins
                INNER JOIN interventions as i ON i.id = ins.intervention_id
                WHERE i.exercice_comptable_id = ?
            ", [$exerciceComptableId]);

        return response()->json(['data' => $data]);
    }
}
