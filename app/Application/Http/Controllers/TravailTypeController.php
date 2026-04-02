<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\TravauxParamBusiness;
use App\Infrastructure\Models\TravailType;
use Illuminate\Http\Request;

class TravailTypeController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $avecTarifs = $admin || in_array('fiche_travail.config', $perms) || in_array('comptabilite.lecture', $perms);

        $type = $avecTarifs ? TravailType::with('fonctions')->get() : TravailType::all();

        return response()->json(['data' => $type]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'type_unite_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $type = TravauxParamBusiness::ajouterType($data);
        return response()->json(['data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'actif' => 'required|boolean',
            'ecriture_categorie_id' => 'required|integer',
            'type_unite_id' => 'integer|required',
            'fonctions.*.type' => 'numeric|required',
            'fonctions.*.tarif' => 'numeric|required',
            'fonctions.*.compte_id' => 'integer|required',
            'fonctions.*.fonction_id' => 'integer|nullable',
        ]);

        $type = TravauxParamBusiness::modifierType($id, $data);
        return response()->json(['data' => $type]);
    }

    public function destroy($id)
    {
        $type = TravauxParamBusiness::supprimerType($id);
        return response()->json(['data' => $type]);
    }
}
