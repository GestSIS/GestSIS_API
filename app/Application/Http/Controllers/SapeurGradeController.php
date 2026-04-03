<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Models\GradeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;

class SapeurGradeController extends Controller
{
    protected $repo;
    protected $business;

    public function __construct(SapeurRepository $repo, SapeurBusiness $business)
    {
        $this->repo = $repo;
        $this->business = $business;
    }

    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $this->repo->getSapeurGradesById($sapeurId)]);
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

        $grade = $this->business->addGrade($sapeurId, $data);
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
        if ($gradeId !== $request->get('id')) {
            return response()->json(['error' => 'invalid grade id']);
        }

        $data = $request->validate([
            'date' => 'date',
            'remarque' => 'string|nullable',
            'id' => 'required|integer|exists:grade_sapeur,id',
            'grade_id' => 'integer|exists:grades,id',
        ]);

        $grade = $this->business->updateGrade($sapeurId, $data);
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

        $res = $this->business->removeGrade($sapeurId, $gradeId);
        return response()->json(['data' => $res]);
    }
}
