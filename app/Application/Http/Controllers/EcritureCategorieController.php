<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Infrastructure\Models\EcritureCategorie;
use Illuminate\Http\Request;

class EcritureCategorieController extends Controller
{
    public function index()
    {
        return response()->json(['data' => EcritureCategorie::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tri' => 'required|numeric',
        ]);

        $categorie = ComptabiliteParamBusiness::ajouterCategorie($data);
        return response()->json(['data' => $categorie]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'tri' => 'required|numeric',
        ]);

        $categorie = ComptabiliteParamBusiness::modifierCategorie($id, $data);
        return response()->json(['data' => $categorie]);
    }

    public function destroy($id)
    {
        ComptabiliteParamBusiness::supprimerCategorie($id);
        return response()->json(['data' => 'ok']);
    }
}
