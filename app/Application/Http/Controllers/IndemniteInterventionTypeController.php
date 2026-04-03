<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Infrastructure\Models\IndemniteInterventionType;
use Illuminate\Http\Request;

class IndemniteInterventionTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => IndemniteInterventionType::with('fonctions')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'type' => 'integer|required',
            'tarif' => 'numeric|required',
            'tarif_pro_rata' => 'boolean|nullable',
            'tarif_min' => 'numeric|nullable',
            'tarif_min_pour' => 'numeric|nullable|required_unless:tarif_min,null',
            'tarif_min_pro_rata' => 'boolean|nullable',
            'taux_weekend' => 'numeric|nullable',
            'taux_nuit' => 'numeric|nullable',
            'debut' => 'date_format:H:i|nullable|required_unless:taux_nuit,null',
            'fin' => 'date_format:H:i|nullable|required_unless:taux_nuit,null',
            'compte_id' => 'integer|required',
            'phase_id' => 'integer|nullable',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.tarif' => 'numeric',
            'fonctions.*.fonction_id' => 'integer',
        ]);

        $indemnite = ComptabiliteParamBusiness::ajouterIndemniteIntervention($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'type' => 'integer|required',
            'tarif' => 'numeric|required',
            'tarif_pro_rata' => 'boolean|nullable',
            'tarif_min' => 'numeric|nullable',
            'tarif_min_pour' => 'numeric|nullable|required_unless:tarif_min,null',
            'tarif_min_pro_rata' => 'boolean|nullable',
            'taux_weekend' => 'numeric|nullable',
            'taux_nuit' => 'numeric|nullable',
            'debut' => 'date_format:H:i|nullable|required_unless:taux_nuit,null',
            'fin' => 'date_format:H:i|nullable|required_unless:taux_nuit,null',
            'compte_id' => 'integer|required',
            'phase_id' => 'integer|nullable',
            'type_unite_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'par_fonction' => 'boolean',
            'fonctions.*.id' => 'numeric|nullable',
            'fonctions.*.tarif' => 'numeric',
            'fonctions.*.fonction_id' => 'integer',
        ]);

        $indemnite = ComptabiliteParamBusiness::modifierIndemniteIntervention($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = ComptabiliteParamBusiness::supprimerIndemniteIntervention($id);
        return response()->json(['data' => $indemnite]);
    }
}
