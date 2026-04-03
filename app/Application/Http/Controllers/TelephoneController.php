<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionParamBusiness;
use App\Models\Telephone;
use Illuminate\Http\Request;

class TelephoneController extends Controller
{
    public function index()
    {
        $telephones = Telephone::all();

        return response()->json(['data' => $telephones]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => 'string|min:1',
            'nom' => 'string|min:1',
            'tri' => 'integer',
        ]);

        $materiel = InterventionParamBusiness::ajouterTelephone($data);
        return response()->json(['data' => $materiel]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'numero' => 'string|min:1',
            'nom' => 'string|min:1',
            'tri' => 'integer',
        ]);

        $materiel = InterventionParamBusiness::modifierTelephone($id, $data);
        return response()->json(['data' => $materiel]);
    }

    public function destroy($id)
    {
        InterventionParamBusiness::supprimerTelephone($id);
        return response()->json(['data' => 'ok']);
    }
}
