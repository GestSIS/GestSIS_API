<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceParamBusiness;
use App\Infrastructure\Models\HeureExerciceType;
use Illuminate\Http\Request;

class HeureExerciceTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => HeureExerciceType::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|required',
            'montant' => 'numeric|required',
            'compte_id' => 'integer|exists:comptes,id|required',
            'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id|required',
            'type_unite_id' => 'integer|exists:type_unites,id|required',
            'type' => 'integer|required',
        ]);
        $type = ExerciceParamBusiness::ajouterHeureExerciceType($data);

        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|required',
            'montant' => 'numeric|required',
            'compte_id' => 'integer|exists:comptes,id|required',
            'ecriture_categorie_id' => 'integer|exists:ecriture_categories,id|required',
            'type_unite_id' => 'integer|exists:type_unites,id|required',
            'type' => 'integer|required',
        ]);
        $type = ExerciceParamBusiness::modifierHeureExerciceType($id, $data);

        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        ExerciceParamBusiness::supprimerHeureExerciceType($id);
        return response()->json(['data' => 'ok']);
    }
}
