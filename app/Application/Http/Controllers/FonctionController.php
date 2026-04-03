<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurParamBusiness;
use App\Models\Fonction;
use Illuminate\Http\Request;

class FonctionController extends Controller
{
    public function index()
    {
        $fonctions = Fonction::all();

        return response()->json(['data' => $fonctions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'string|min:1|required',
            'abreviation' => 'string|min:1|required',
            'cumulable' => 'boolean|required',
            'actif' => 'boolean|required',
            'tri' => 'integer|required'
        ]);

        $fonction = SapeurParamBusiness::ajouterFonction($data);
        return response()->json(['data' => $fonction]);
    }

    public function update(Request $request, $id)
    {
        if (!Fonction::where('id', $id)->exists()) {
            return response()->json(['error' => 'Fonction not found'], 404);
        }

        $data = $request->validate([
            'nom' => 'string|min:1|required',
            'abreviation' => 'string|min:1|required',
            'cumulable' => 'boolean|required',
            'actif' => 'boolean|required',
            'tri' => 'integer|required'
        ]);

        $fonction = SapeurParamBusiness::modifierFonction($id, $data);
        return response()->json(['data' => $fonction]);
    }

    public function destroy($id)
    {
        if (!Fonction::where('id', $id)->exists()) {
            return response()->json(['error' => 'Fonction not found'], 404);
        }

        $fonction = SapeurParamBusiness::supprimerFonction($id);
        return response()->json(['data' => $fonction]);
    }
}
