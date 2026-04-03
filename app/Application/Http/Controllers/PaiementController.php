<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\PaiementBusiness;
use App\Models\Decompte;
use App\Models\Paiement;

class PaiementController extends Controller
{
    /**
     * Créer un fichier iso20022 pour un paiement
     *
     * @param int $id du paiement pour lequelle le fichier doit être créé
     */
    public function iso20022($id)
    {
        return PaiementBusiness::iso20022PourPaiementStream($id);
    }

    /**
     * Retourne un paiement
     * $id - id du paiement souhaité
     */
    public function get($id)
    {
        return response()->json(['data' => Paiement::find($id)]);
    }

    public function getByExerciceComptable($exerciceComptableId)
    {
        return response()->json(['data' => Decompte::where('exercice_comptable_id', $exerciceComptableId)->with('paiements')->get()]);
    }
}
