<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Infrastructure\Models\StatFederal;
use Illuminate\Http\Request;

class StatFederalController extends Controller
{
    public function index()
    {
        $statsFederal = StatFederal::all();

        return response()->json(['data' => $statsFederal]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'statut' => 'integer',
            'tri' => 'integer'
        ]);

        $stat = InterventionParamBusiness::ajouterStatFederal($data);
        return response()->json(['data' => $stat]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'statut' => 'integer',
            'tri' => 'integer'
        ]);

        $stat = InterventionParamBusiness::modifierStatFederal($id, $data);
        return response()->json(['data' => $stat]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerStatFederal($id);
        return response()->json(['data' => 'ok']);
    }
}
