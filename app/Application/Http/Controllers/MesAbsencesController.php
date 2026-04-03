<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\AbsenceBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Absence;
use App\Infrastructure\Models\ExerciceComptable;
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
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
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
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $request->validate([
            'debut' => 'date',
            'fin' => 'date',
        ]);

        $data['sapeur_id'] = $sapeurId;
        $absence = AbsenceBusiness::ajouterAbsence($data);
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

        $absence = Absence::find($absenceId);
        if ($absence->sapeur_id !== $sapeurId) {
            throw new ArrayException([], 'Absence invalide');
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
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $absence = Absence::find($absenceId);
        if ($absence?->sapeur_id !== $sapeurId) {
            throw new ArrayException([], 'Absence invalide');
        }
        AbsenceBusiness::supprimerAbsence($absenceId);
        return response()->json(['data' => null]);
    }
}
