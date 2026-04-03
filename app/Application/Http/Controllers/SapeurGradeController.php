<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Models\GradeSapeur;
use App\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurGradeController extends Controller
{

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => GradeSapeur::where('sapeur_id', $sapeurId)->get()]);
    }

    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $data = $request->validate([
            'grade_id' => 'required|integer|exists:grades,id',
            'date' => 'required|date',
            'remarque' => 'string|nullable',
        ]);

        $grade = SapeurBusiness::addGrade($sapeurId, $data);
        return response()->json(['data' => $grade]);
    }

    public function update(Request $request, int $sapeurId, int $gradeId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!GradeSapeur::where(['id' => $gradeId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Grade non trouvé'], 404);
        }
        if ($gradeId !== $request->input('id')) {
            return response()->json(['error' => 'invalid grade id']);
        }

        $data = $request->validate([
            'date' => 'date',
            'remarque' => 'string|nullable',
            'id' => 'required|integer|exists:grade_sapeur,id',
            'grade_id' => 'integer|exists:grades,id',
        ]);

        $grade = SapeurBusiness::updateGrade($sapeurId, $data);
        return response()->json(['data' => $grade]);
    }

    public function destroy(int $sapeurId, int $gradeId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        if (!GradeSapeur::where(['id' => $gradeId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Grade non trouvé'], 404);
        }

        $res = SapeurBusiness::removeGrade($sapeurId, $gradeId);
        return response()->json(['data' => $res]);
    }
}
