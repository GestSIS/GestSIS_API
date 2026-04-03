<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Models\IndemniteCoursType;
use Illuminate\Http\Request;

class IndemniteCoursTypeController extends Controller
{
    /**
     * Liste des indemnités de cours types
     */
    public function index()
    {
        return IndemniteCoursType::with('fonctions')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'ecriture_categorie_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
        ]);

        $indemnite = ComptabiliteParamBusiness::ajouterIndemniteCoursType($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'ecriture_categorie_id' => 'integer|required',
            'fonctions.*.type' => 'integer|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
            'fonctions.*.type_unite_id' => 'integer|required',
        ]);

        $indemnite = ComptabiliteParamBusiness::modifierIndemniteCoursType($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    /**
     * Retourne un type
     *
     * @param int $id id du type souhaité
     */
    public function destroy($id)
    {
        ComptabiliteParamBusiness::supprimerIndemniteCoursType($id);
        return response()->json(['data' => 'ok']);
    }
}
