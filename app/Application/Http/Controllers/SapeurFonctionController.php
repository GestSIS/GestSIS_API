<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\FonctionSapeur;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurFonctionController extends Controller
{

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => FonctionSapeur::where('sapeur_id', $sapeurId)->get()]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'fonction_id' => 'required|integer|exists:fonctions,id',
            'debut' => 'required|date',
            'fin' => 'date|nullable|after_or_equal:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = SapeurBusiness::addFonction($sapeurId, $data);
        return response()->json(['data' => $fonction]);
    }

    public function update(Request $request, int $sapeurId, int $fonctionId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!FonctionSapeur::where(['id' => $fonctionId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Fonction non trouvée'], 404);
        }
        if ($fonctionId !== $request->get('id')) {
            return response()->json(['error' => 'invalid fonction id']);
        }

        $data = $request->validate([
            'id' => 'required|integer|exists:fonction_sapeur,id',
            'debut' => 'date',
            'fin' => 'date|nullable|after:debut',
            'remarque' => 'string|nullable',
        ]);

        $fonction = SapeurBusiness::updateFonction($sapeurId, $data);
        return response()->json(['data' => $fonction]);
    }

    public function destroy(int $sapeurId, int $fonctionId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!FonctionSapeur::where(['id' => $fonctionId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Fonction non trouvée'], 404);
        }

        $res = SapeurBusiness::removeFonction($sapeurId, $fonctionId);
        return response()->json(['data' => $res]);
    }

    public function fin(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'ids.*' => 'required|integer',
            'date' => 'required|date'
        ]);

        $fonctions = SapeurBusiness::finFonctions($sapeurId, $data['date'], $data['ids']);
        return response()->json(['data' => $fonctions]);
    }
}
