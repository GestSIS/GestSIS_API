<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ComptabiliteParamBusiness;
use App\Domaine\Business\ImputationBusiness;
use App\Models\Compte;
use App\Models\Ecriture;
use Illuminate\Http\Request;

class CompteController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Compte::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'numero' => 'string|required',
            'produit' => 'boolean|required',
        ]);

        $compte = ComptabiliteParamBusiness::ajouterCompte($data);
        return response()->json(['data' => $compte]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'numero' => 'string|required',
            'produit' => 'boolean|required',
        ]);

        $compte = ComptabiliteParamBusiness::modifierCompte($id, $data);
        return response()->json(['data' => $compte]);
    }

    public function destroy($id)
    {
        ComptabiliteParamBusiness::supprimerCompte($id);
        return response()->json(['data' => 'ok']);
    }

    public function ecritures(int $id, int $exerciceComptableId)
    {
        return response()->json([
            'data' => Ecriture::where('exercice_comptable_id', $exerciceComptableId)
                ->where('compte_id', $id)->get()
        ]);
    }

    public function justificatifIndividuel(Request $request, int $exerciceComptableId, int $compteId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return ImputationBusiness::justificatifIndividuel($exerciceComptableId, $compteId, $sisKey);
    }

    public function justificatifComplet(Request $request, int $exerciceComptableId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return ImputationBusiness::justificatifComplet($exerciceComptableId, $sisKey);
    }
}
