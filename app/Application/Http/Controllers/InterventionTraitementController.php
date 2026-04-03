<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Models\InterventionTraitement;
use Illuminate\Http\Request;

class InterventionTraitementController extends Controller
{
    public function index()
    {
        $traitements = InterventionTraitement::all();

        return response()->json(['data' => $traitements]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $traitement = InterventionParamBusiness::ajouterTraitement($data);
        return response()->json(['data' => $traitement]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'tri' => 'integer'
        ]);

        $traitement = InterventionParamBusiness::modifierTraitement($id, $data);
        return response()->json(['data' => $traitement]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerTraitement($id);
        return response()->json(['data' => 'ok']);
    }
}
