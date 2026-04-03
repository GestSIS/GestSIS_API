<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\CoursSapeur;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurCoursController extends Controller
{

    public function index($sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => CoursSapeur::where('sapeur_id', $sapeurId)->get()]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'date' => 'required|date',
            'duree' => 'required|numeric|min:0',
            'localite_id' => 'integer|exists:localites,id',
            'cours_id' => 'required|integer|exists:cours,id',
            'fonction_sapeur_id' => 'integer|nullable',
            'fonction_id' => 'integer|nullable',
            'grade_id' => 'integer|nullable',
            'date_fonction' => 'bail|required_with:fonction_id|date|nullable',
            'date_grade' => 'bail|required_with:grade_id|date|nullable'
        ]);

        $cours = SapeurBusiness::addCours($sapeurId, $data);
        return response()->json(['data' => $cours]);
    }

    public function update(Request $request, int $sapeurId, int $coursId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!CoursSapeur::where(['id' => $coursId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Cours non trouvé'], 404);
        }
        if ($coursId !== $request->get('id')) {
            return response()->json(['error' => 'invalid cours id']);
        }

        $data = $request->validate([
            'id' => 'integer|exists:cours_sapeur,id',
            'duree' => 'numeric|min:0',
            'date' => 'date',
            'localite_id' => 'integer|exists:localites,id',
        ]);

        $cours = SapeurBusiness::updateCours($sapeurId, $data);
        return response()->json(['data' => $cours]);
    }

    public function destroy(int $sapeurId, int $coursId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!CoursSapeur::where(['id' => $coursId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Cours non trouvé'], 404);
        }

        SapeurBusiness::removeCours($sapeurId, $coursId);
        return response()->json(['data' => 'success']);
    }
}
