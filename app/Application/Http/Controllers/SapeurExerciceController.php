<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ExerciceService;
use Illuminate\Http\Response;

class SapeurExerciceController extends Controller
{
    protected $service;

    public function __construct(ExerciceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($sapeurId, $exerciceComptableId)
    {
        $cours = $this->service->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);

        return response()->json(['data' => $cours]);
    }

    /**
     * Return le nombre d'intervention par materiel pour l'année comptable
     *
     * @param Request $request
     * @param int $exercice_comptable_id
     * @return Response
     */
    public function stat(int $exercice_comptable_id)
    {
        $data = $this->service->statPresences($exercice_comptable_id);

        return response()->json(['data' => $data]);
    }
}
