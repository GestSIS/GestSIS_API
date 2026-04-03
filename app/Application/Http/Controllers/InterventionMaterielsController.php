<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Models\InterventionMateriel;
use Illuminate\Http\Request;

class InterventionMaterielsController extends Controller
{

    public function index($intervention_id)
    {
        return response()->json(['data' => InterventionMateriel::where('intervention_id', $intervention_id)->get()]);
    }

    public function store(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'materiels.*.materiel_id' => 'required|exists:materiels,id',
            'materiels.*.quantite' => 'required|numeric|min:1'
        ]);

        InterventionBusiness::addMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => InterventionMateriel::where('intervention_id', $intervention_id)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'materiels.*.id' => 'required|exists:intervention_materiel,id',
            'materiels.*.quantite' => 'required|numeric|min:1'
        ]);

        InterventionBusiness::updateMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => InterventionMateriel::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['materiels.*' => 'required|exists:intervention_materiel,id']);
        InterventionBusiness::removeMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => 'success']);
    }
}
