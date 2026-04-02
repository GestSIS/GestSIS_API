<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Infrastructure\Models\TypeIntervention;
use Illuminate\Http\Request;

class TypeInterventionController extends Controller
{
    public function index()
    {
        $types = TypeIntervention::all();

        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer',
            'stat_intervention_id' => 'integer'
        ]);

        $type = InterventionParamBusiness::ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer',
            'stat_intervention_id' => 'integer'
        ]);

        $type = InterventionParamBusiness::modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerType($id);
        return response()->json(['data' => 'ok']);
    }
}
