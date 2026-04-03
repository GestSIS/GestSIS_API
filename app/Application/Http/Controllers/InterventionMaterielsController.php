<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\InterventionMateriel;
use Illuminate\Http\Request;

class InterventionMaterielsController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

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

        $this->business->addMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => InterventionMateriel::where('intervention_id', $intervention_id)->get()]);
    }

    public function update(Request $request, int $intervention_id)
    {
        $data = $request->validate([
            'materiels.*.id' => 'required|exists:intervention_materiel,id',
            'materiels.*.quantite' => 'required|numeric|min:1'
        ]);

        $this->business->updateMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => InterventionMateriel::where('intervention_id', $intervention_id)->get()]);
    }

    public function destroy(Request $request, int $intervention_id)
    {
        $data = $request->validate(['materiels.*' => 'required|exists:intervention_materiel,id']);
        $this->business->removeMateriels($intervention_id, $data['materiels']);
        return response()->json(['data' => 'success']);
    }
}
