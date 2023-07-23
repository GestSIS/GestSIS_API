<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;

class MesAbsencesController extends Controller
{
    private $service = null;

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
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

        $data = $this->service->mesAbsences($sapeurId, $exerciceComptableId);
        return response()->json(['data' => $data]);
    }

    /**
     * Saisir une absence
     */
    public function store(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $request->validate([
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = $this->service->creerAbsence($sapeurId, $data);
        return response()->json(['data' => $absence]);
    }

    /**
     * Modifier une absence
     */
    public function update(Request $request, int $absenceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $request->validate([
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $absence = $this->service->modifierAbsence($sapeurId, $absenceId, $data);
        return response()->json(['data' => $absence]);
    }

    /**
     * Supprimer une absence
     */
    public function delete(Request $request, int $absenceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $absence = $this->service->supprimerAbsence($sapeurId, $absenceId);
        return response()->json(['data' => $absence]);
    }
}
