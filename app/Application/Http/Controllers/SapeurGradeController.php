<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\GradeSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurGradeController extends Controller
{
    protected $service;

    public function __construct(SapeurService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $grades = $this->service->getSapeurGradesById($sapeurId);

        return response()->json(['data' => $grades]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ArrayException
     */
    public function store(Request $request, int $sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        // Ajout d'un nouveau grade
        $data = $request->validate([
            'grade_id' => 'required|integer|exists:grades,id',
            'date' => 'required|date',
            'remarque' => 'string|nullable',
        ]);

        $grade = $this->service->addGrade($sapeurId, $data);

        return response()->json(['data' => $grade]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $gradeId
     * @return Response
     * @throws ArrayException
     */
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
            'gade_id' => 'integer|exists:grades,id',
        ]);

        $grade = $this->service->updateGrade($sapeurId, $data);

        return response()->json(['data' => $grade]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $gradeId
     * @return Response
     */
    public function destroy(int $sapeurId, int $gradeId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        if (!GradeSapeur::where(['id' => $gradeId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Grade non trouvé'], 404);
        }

        $res = $this->service->removeGrade($sapeurId, $gradeId);

        return response()->json(['data' => $res]);
    }
}
