<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Models\StatIntervention;
use Illuminate\Http\Request;

class StatInterventionController extends Controller
{
    public function index()
    {
        $stats = StatIntervention::all();

        return response()->json(['data' => $stats]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $stat = InterventionParamBusiness::ajouterStat($data);
        return response()->json(['data' => $stat]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $stat = InterventionParamBusiness::modifierStat($id, $data);
        return response()->json(['data' => $stat]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerStat($id);
        return response()->json(['data' => 'ok']);
    }
}
