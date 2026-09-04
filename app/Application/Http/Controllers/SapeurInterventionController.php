<?php

namespace App\Application\Http\Controllers;

use App\Models\InterventionSapeur;

class SapeurInterventionController extends Controller
{
    /**
     * Récupération des interventions auxquelles un sapeur a participé, pour un
     * exercice comptable donné.
     */
    public function index($sapeurId, $exerciceComptableId)
    {
        $data = InterventionSapeur::where('sapeur_id', $sapeurId)
            ->whereHas('intervention', fn($query) => $query->where('exercice_comptable_id', $exerciceComptableId))
            ->with('intervention')
            ->get();

        return response()->json(['data' => $data]);
    }
}
