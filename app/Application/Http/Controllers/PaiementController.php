<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\PaiementBusiness;
use App\Infrastructure\Models\Decompte;
use App\Infrastructure\Models\Paiement;

class PaiementController extends Controller
{
    protected $business;

    public function __construct(PaiementBusiness $business)
    {
        $this->business = $business;
    }

    /**
     * Créer un fichier iso20022 pour un paiement
     *
     * @param int $id du paiement pour lequelle le fichier doit être créé
     */
    public function iso20022($id)
    {
        return $this->business->iso20022PourPaiementStream($id);
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
