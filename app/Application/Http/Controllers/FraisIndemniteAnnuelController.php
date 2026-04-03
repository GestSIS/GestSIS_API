<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Infrastructure\Models\FraisIndemniteAnnuelType;
use Illuminate\Http\Request;

class FraisIndemniteAnnuelController extends Controller
{
    public function index()
    {
        return response()->json(['data' => FraisIndemniteAnnuelType::with('fraisIndemniteAnnuels')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'frais_indemnite_annuel_type_id' => 'exists:frais_indemnite_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
        ]);

        $indemnite = ComptabiliteParamBusiness::ajouterFraisIndemniteAnnuel($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'montant' => 'numeric',
            'quantite' => 'numeric',
            'fonction_id' => 'exists:fonctions,id',
            'frais_indemnite_annuel_type_id' => 'exists:frais_indemnite_annuel_types,id',
            'type_unite_id' => 'exists:type_unites,id'
        ]);

        $indemnite = ComptabiliteParamBusiness::modifierFraisIndemniteAnnuel($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = ComptabiliteParamBusiness::supprimerFraisIndemniteAnnuel($id);
        return response()->json(['data' => $indemnite]);
    }
}
