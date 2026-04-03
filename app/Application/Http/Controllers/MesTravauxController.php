<?php

namespace App\Application\Http\Controllers;

use App\Models\Travail;
use Illuminate\Http\Request;

class MesTravauxController extends Controller
{
    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = Travail::where([
            ['sapeur_id', '=', $sapeurId],
            ['exercice_comptable_id', '=', $exerciceComptableId]
        ])->get();
        return response()->json(['data' => $data]);
    }
}
