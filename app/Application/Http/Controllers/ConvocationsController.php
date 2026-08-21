<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use Illuminate\Http\Request;

/**
 * Controller pour la convocation de sapeurs à des exercices
 * TODO: Fusionner avec ConvocationController
 */
class ConvocationsController extends Controller
{

    public function index(Request $request, $exerciceId)
    {
        $perms = $request->attributes->get('permissions', []);
        $hasPresencePermission = in_array('exercice.presence', $perms);
        $sapeurs = ExerciceBusiness::listeSapeurOfExerciceById($exerciceId, $hasPresencePermission);

        return response()->json(['data' => $sapeurs]);
    }

    public function convocationPdf(Request $request, $exerciceComptableId)
    {
        $request->merge([
            'affichage_duree' => (bool) $request->input('affichage_duree', true),
            'affichage_pour_info' => (bool) $request->input('affichage_pour_info', false),
            'sapeurIds' => is_string($request->input('sapeurIds', '')) ? explode(',', $request->input('sapeurIds', '')) : $request->input('sapeurIds', ''),
        ]);

        $sapeurIds = $request->validate([
            'sapeurIds' => 'array|nullable',
            'sapeurIds.*' => 'integer'
        ]);

        if (count($sapeurIds['sapeurIds']) === 1 && $sapeurIds['sapeurIds'][0] === "") {
            $sapeurIds['sapeurIds'] = [];
        }
        $sapeurIds = $sapeurIds['sapeurIds'] ?? [];

        $sisKey = $request->header('Sis-Key', Null);
        return ExerciceBusiness::convoquer($exerciceComptableId, $sapeurIds, $sisKey);
    }

    public function store(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.absent' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.amende' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
        ]);

        $statut = ExerciceBusiness::addSapeurs($exerciceId, $data['sapeurs']);
        return response()->json([
            'data' => [
                "statut" => $statut,
                "sapeurs" => ExerciceBusiness::listeSapeurOfExerciceById($exerciceId),
            ]
        ]);
    }

    public function updatePresence(Request $request, $id)
    {
        $request->merge([
            'id' => (int) $request->input('id'),
            'convoque' => (int) $request->input('convoque'),
            'present' => (int) $request->input('present'),
            'absent' => (int) $request->input('absent'),
            'remplace' => (int) $request->input('remplace'),
            'excuse_type_id' => (int) $request->input('excuse_type_id'),
            'excuse_statut' => (int) $request->input('excuse_statut'),
        ]);

        $data = $request->validate([
            'convoque' => 'required|integer',
            'present' => 'required|integer',
            'absent' => 'required|integer',
            'remplace' => 'required|integer',
            'excuse_type_id' => 'nullable|integer',
            'remarque' => 'nullable|string|max:1000',
            'excuse_statut' => 'integer',
            'justification' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif_file invalide']);
        }

        $file = $request->file('justificatif_file');
        $sisKey = $request->header('Sis-Key', Null);
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = in_array('exercice.validation', $perms);

        $statut = ExerciceBusiness::updatePresence($id, $data, $file, $hasValidationPermission, $sisKey);
        $exerciceSapeur = \App\Models\ExerciceSapeur::with('exercice')->find($id);
        return response()->json([
            'data' => [
                'statut' => $statut,
                'sapeur' => ExerciceBusiness::sapeurOfExerciceById($exerciceSapeur->exercice_id, $exerciceSapeur->sapeur_id),
            ]
        ]);
    }

    public function updatePresences(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*.id' => 'nullable|integer',
            'sapeurs.*.sapeur_id' => 'integer|required',
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.absent' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
            'sapeurs.*.heures.*.id' => 'nullable|integer',
            'sapeurs.*.heures.*.quantite' => 'nullable|numeric',
            'sapeurs.*.heures.*.heure_exercice_type_id' => 'nullable|integer',
        ]);

        $statut = ExerciceBusiness::updatePresences($exerciceId, $data['sapeurs']);
        return response()->json([
            'data' => [
                'statut' => $statut,
                'sapeurs' => ExerciceBusiness::listeSapeurOfExerciceById($exerciceId),
            ]
        ]);
    }

    public function destroy(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*' => 'integer'
        ]);

        $statut = ExerciceBusiness::removeSapeurs($exerciceId, $data['sapeurs']);
        return response()->json(['data' => $statut]);
    }

    public function createHeure(Request $request)
    {
        $data = $request->validate([
            'exercice_id' => 'integer|required',
            'sapeur_id' => 'integer|required',
            'quantite' => 'nullable|numeric',
            'heure_exercice_type_id' => 'nullable|integer',
        ]);

        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = in_array('exercice.validation', $perms);
        $heure = ExerciceBusiness::createHeure($data, $hasValidationPermission);

        return response()->json(['data' => $heure]);
    }

    public function updateHeure(Request $request, int $heureId)
    {
        $data = $request->validate([
            'id' => 'integer|required',
            'quantite' => 'nullable|numeric',
        ]);

        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = in_array('exercice.validation', $perms);
        $heure = ExerciceBusiness::updateHeure($heureId, $data, $hasValidationPermission);

        return response()->json(['data' => $heure]);
    }

    public function destroyHeure(Request $request, int $heureId)
    {
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = in_array('exercice.validation', $perms);
        $statut = ExerciceBusiness::removeHeure($heureId, $hasValidationPermission);

        return response()->json(['data' => $statut]);
    }

    public function supprimerConvocations(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'convocations' => 'array|required',
            'convocations.*' => 'integer'
        ]);

        $statut = ExerciceBusiness::supprimerConvocations($sapeurId, $data['convocations']);
        return response()->json(['data' => $statut]);
    }
}
