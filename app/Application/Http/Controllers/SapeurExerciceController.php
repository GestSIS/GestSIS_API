<?php

namespace App\Application\Http\Controllers;

use App\Domaine\SPI\ExerciceRepository;
use Illuminate\Support\Facades\DB;

class SapeurExerciceController extends Controller
{
    protected $exerciceRepo;

    public function __construct(ExerciceRepository $exerciceRepo)
    {
        $this->exerciceRepo = $exerciceRepo;
    }

    public function index($sapeurId, $exerciceComptableId)
    {
        $cours = $this->exerciceRepo->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);
        return response()->json(['data' => $cours]);
    }

    public function stat(int $exercice_comptable_id)
    {
        $data = DB::select("SELECT es.*
                FROM exercice_sapeur as es
                INNER JOIN exercices as e ON e.id = es.exercice_id
                WHERE e.exercice_comptable_id = ?
            ", [$exercice_comptable_id]);

        return response()->json(['data' => $data]);
    }
}
