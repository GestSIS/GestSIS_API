<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Models\FraisIndemniteAnnuelType;
use Illuminate\Http\Request;

class FraisIndemniteAnnuelTypeController extends Controller
{
    public function index()
    {
        return response()->json(['data' => FraisIndemniteAnnuelType::with('fraisIndemniteAnnuels')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'compte_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'cumulable' => 'boolean',
            'type' => 'integer|required',
        ]);

        $indemnite = ComptabiliteParamBusiness::ajouterFraisIndemniteAnnuelType($data);
        return response()->json(['data' => $indemnite]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'compte_id' => 'integer|required',
            'ecriture_categorie_id' => 'integer|required',
            'cumulable' => 'boolean',
            'type' => 'integer|required',
        ]);

        $indemnite = ComptabiliteParamBusiness::modifierFraisIndemniteAnnuelType($id, $data);
        return response()->json(['data' => $indemnite]);
    }

    public function destroy($id)
    {
        $indemnite = ComptabiliteParamBusiness::supprimerFraisIndemniteAnnuelType($id);
        return response()->json(['data' => $indemnite]);
    }
}
