<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\GroupeSapeur;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurGroupeController extends Controller
{

    public function index(int $sapeurId)
    {
        if (!Sapeur::whereId($sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => GroupeSapeur::where('sapeur_id', $sapeurId)->get()]);
    }

    public function quitter(Request $request, int $sapeurId)
    {
        if (!Sapeur::whereId($sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'groupes.*' => 'required|integer'
        ]);

        $groupes = SapeurBusiness::removeGroupes($sapeurId, $data['groupes']);
        return response()->json(['data' => $groupes]);
    }
}
