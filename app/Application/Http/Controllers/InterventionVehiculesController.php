<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Models\InterventionVehicule;
use Illuminate\Http\Request;

class InterventionVehiculesController extends Controller
{
    public function index($interventionId)
    {
        return response()->json(['data' => InterventionVehicule::where('intervention_id', $interventionId)->get()]);
    }

    public function store(Request $request, int $interventionId)
    {
        $data = $request->validate(['vehicules.*' => 'required|integer']);
        InterventionBusiness::addVehicules($interventionId, $data['vehicules']);
        return response()->json(['data' => InterventionVehicule::where('intervention_id', $interventionId)->get()]);
    }

    public function destroy(Request $request, int $interventionId)
    {
        $data = $request->validate(['vehicules.*' => 'required|integer']);
        InterventionBusiness::removeVehicules($interventionId, $data['vehicules']);
        return response()->json(['data' => 'success']);
    }
}
