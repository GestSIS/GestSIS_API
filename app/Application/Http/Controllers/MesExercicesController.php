<?php

namespace App\Application\Http\Controllers;

use App\Domaine\SPI\ExerciceRepository;
use Illuminate\Http\Request;

class MesExercicesController extends Controller
{
    private $exerciceRepo;

    public function __construct(ExerciceRepository $exerciceRepo)
    {
        $this->exerciceRepo = $exerciceRepo;
    }

    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->exerciceRepo->listExerciceOfSapeurById($exerciceComptableId, $sapeurId);
        return response()->json(['data' => $data]);
    }
}
