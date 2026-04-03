<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Infrastructure\Models\IndemniteExerciceType;
use Illuminate\Http\Request;

class IndemniteExerciceTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => IndemniteExerciceType::with('fonctions')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $indemnite = ComptabiliteParamBusiness::ajouterIndemniteExercice($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.type' => 'integer|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable'
        ]);

        $indemnite = ComptabiliteParamBusiness::modifierIndemniteExercice($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = ComptabiliteParamBusiness::supprimerIndemniteExercice($id);
        return response()->json(['data' => $indemnite]);
    }
}
