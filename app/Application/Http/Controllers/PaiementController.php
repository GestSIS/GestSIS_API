<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Infrastructure\Models\Paiement;
use App\Infrastructure\Models\Decompte;

class PaiementController extends Controller
{
    /**
     * Retourne tous les paiement
     */
    public function getAll(){
        $paiements = Paiement::all();

        return response()->json(['data' => $paiements]);
    }

    /**
     * Retourne un paiement
     * $id - id du paiement souhaité
     */
    public function get($id){
        $paiements = Paiement::find($id);

        return response()->json(['data' => $paiements]);
    }

    /**
     * Retourne tous les paiements pour un décompte
     * $id - id du paiement
     */
    public function getByDecompte($id){
        $paiements = Paiement::where('decompte_id', $id)->get();

        return response()->json(['data' => $paiements]);
    }

    /**
     * Retourne tous les paiements pour un exercice comptable
     * $id - id de l'exercice comptable
     */
    public function getByExerciceComptable($id){
        $decomptes = Decompte::where('exercice_comptable_id', $id)->with('paiements')->get();

        return response()->json(['data' => $decomptes]);
    }
}
