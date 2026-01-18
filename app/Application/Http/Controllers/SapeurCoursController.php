<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\SapeurService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\Sapeur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SapeurCoursController extends Controller
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
    public function index($sapeurId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        $cours = $this->service->getSapeurCoursById($sapeurId);

        return response()->json(['data' => $cours]);
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

        $cours = $this->service->addCours($sapeurId, $data);
        return response()->json(['data' => $cours]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $sapeurId
     * @param int $coursId
     * @return Response
     * @throws ArrayException
     */
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

        $cours = $this->service->updateCours($sapeurId, $data);
        return response()->json(['data' => $cours]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $sapeurId
     * @param int $coursId
     * @return Response
     */
    public function destroy(int $sapeurId, int $coursId)
    {
        if (!Sapeur::where('id', $sapeurId)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }

        if (!CoursSapeur::where(['id' => $coursId, 'sapeur_id' => $sapeurId])->exists()) {
            return response()->json(['error' => 'Cours non trouvé'], 404);
        }

        $this->service->removeCours($sapeurId, $coursId);

        return response()->json(['data' => 'success']);
    }
}
