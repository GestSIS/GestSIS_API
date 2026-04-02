<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceParamBusiness;
use App\Infrastructure\Models\ExerciceCategorie;
use Illuminate\Http\Request;

class ExerciceCategorieController extends Controller
{
    public function index()
    {
        $categorie = ExerciceCategorie::all();

        return response()->json(['data' => $categorie]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'amendable' => 'boolean|required',
            'duree_base' => 'integer|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required'
        ]);

        $categorie = ExerciceParamBusiness::ajouterCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'amendable' => 'boolean|required',
            'duree_base' => 'integer|required',
            'statut' => 'integer|required',
            'tri' => 'integer|required'
        ]);

        $categorie = ExerciceParamBusiness::modifierCategorie($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        ExerciceParamBusiness::supprimerCategorie($id);
        return response()->json(['data' => 'ok']);
    }
}
