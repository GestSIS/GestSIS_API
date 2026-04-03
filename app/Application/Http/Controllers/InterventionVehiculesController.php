<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Infrastructure\Models\InterventionVehicule;
use Illuminate\Http\Request;

class InterventionVehiculesController extends Controller
{
    protected $business;

    public function __construct(InterventionBusiness $business)
    {
        $this->business = $business;
    }

    public function index($interventionId)
    {
        return response()->json(['data' => InterventionVehicule::where('intervention_id', $interventionId)->get()]);
    }

    public function store(Request $request, int $interventionId)
    {
        $data = $request->validate(['vehicules.*' => 'required|integer']);
        $this->business->addVehicules($interventionId, $data['vehicules']);
        return response()->json(['data' => InterventionVehicule::where('intervention_id', $interventionId)->get()]);
    }

    public function destroy(Request $request, int $interventionId)
    {
        $data = $request->validate(['vehicules.*' => 'required|integer']);
        $this->business->removeVehicules($interventionId, $data['vehicules']);
        return response()->json(['data' => 'success']);
    }
}
