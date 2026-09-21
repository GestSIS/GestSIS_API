<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AbsenceBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Absence;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;

class MesAbsencesController extends Controller
{
    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['message' => 'Votre compte n\'est pas lié à un sapeur'], 422);
        }

        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable === null) {
            return response()->json(['message' => 'Exercice comptable introuvable'], 422);
        }
        $data = Absence::where('sapeur_id', '=', $sapeurId)->where([
            ['debut', '<', $exerciceComptable->fin],
            ['fin', '>', $exerciceComptable->debut]
        ])->get();
        return response()->json(['data' => $data]);
    }

    /**
     * Saisir une absence
     */
    public function store(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['message' => 'Votre compte n\'est pas lié à un sapeur'], 422);
        }

        $data = $request->validate([
            'debut' => 'required|date',
            'fin' => 'required|date',
        ]);

        $data['sapeur_id'] = $sapeurId;
        $absence = AbsenceBusiness::ajouterAbsence($data);
        return response()->json(['data' => $absence], 201);
    }

    /**
     * Modifier une absence
     */
    public function update(Request $request, int $absenceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['message' => 'Votre compte n\'est pas lié à un sapeur'], 422);
        }

        $data = $request->validate([
            'debut' => 'required|date',
            'fin' => 'required|date',
        ]);

        $absence = Absence::find($absenceId);
        if ($absence?->sapeur_id !== $sapeurId) {
            return response()->json(['message' => 'Absence invalide'], 422);
        }
        $data['sapeur_id'] = $sapeurId;
        $absence = AbsenceBusiness::modifierAbsence($absenceId, $data);
        return response()->json(['data' => $absence]);
    }

    /**
     * Supprimer une absence
     */
    public function delete(Request $request, int $absenceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['message' => 'Votre compte n\'est pas lié à un sapeur'], 422);
        }

        $absence = Absence::find($absenceId);
        if ($absence?->sapeur_id !== $sapeurId) {
            return response()->json(['message' => 'Absence invalide'], 422);
        }
        AbsenceBusiness::supprimerAbsence($absenceId);
        return response()->json(['data' => null]);
    }
}
