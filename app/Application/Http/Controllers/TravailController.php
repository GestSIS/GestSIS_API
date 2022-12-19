<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\TravauxService;
use Illuminate\Http\Request;

class TravailController extends Controller
{
    protected $service;

    public function __construct(TravauxService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, $exerciceComptableId)
    {
        // Auteur
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasLectureOuValidationPermission = $admin || in_array('fiche_travail.validation', $perms) || in_array('fiche_travail.lecture', $perms);
        $withEcritures = $admin || in_array('comptabilite.tout', $perms);

        $sapeurId = $request->attributes->get('sapeurId');
        if (!$hasLectureOuValidationPermission && !$sapeurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }
        $travaux = $this->service->travaux($exerciceComptableId, $hasLectureOuValidationPermission ? null : $sapeurId, $withEcritures);

        return response()->json(['data' => $travaux]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travaux' => 'array',
            'travaux.*.designation' => 'required|string|min:1',
            'travaux.*.date' => 'required|date',
            'travaux.*.quantite' => 'required|numeric',
            'travaux.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travaux.*.travail_type_id' => 'required|integer|exists:travail_types,id',
            'travaux.*.exercice_comptable_id' => 'required|integer|exists:exercice_comptables,id',
        ]);

        // Auteur
        $sapeurId = $request->attributes->get('sapeurId');
        if (!$sapeurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);

        $hasSaisieCommunePermission = $admin || in_array('fiche_travail.saisie_commune', $perms);

        $travail = $this->service->ajouter($data['travaux'], $sapeurId, $hasSaisieCommunePermission);
        return response()->json(['data' => $travail]);
    }

    public function update(Request $request, $travailId)
    {
        $data = $request->validate([
            'designation' => 'required|string|min:1',
            'date' => 'required|date',
            'quantite' => 'required|numeric',
            'sapeur_id' => 'required|integer|exists:sapeurs,id',
            'travail_type_id' => 'required|integer|exists:travail_types,id',
        ]);

        $sapeurId = $request->attributes->get('sapeurId', []);

        $travail = $this->service->modifier($travailId, $data, $sapeurId);
        return response()->json(['data' => $travail]);
    }

    public function review(Request $request, $travailId)
    {
        $data = $request->validate([
            'justification' => 'string|nullable',
            'accepte' => 'boolean|required',
            // 'quantite' => 'decimal|nullable',
        ]);

        $travail = $this->service->review($travailId, $data['accepte'], $data['justification'] ?? '', $data['quantite'] ?? null);
        return response()->json(['data' => $travail]);
    }

    public function cancelReview(Request $request, $travailId)
    {
        $travail = $this->service->cancelReview($travailId);
        return response()->json(['data' => $travail]);
    }

    public function destroy(Request $request, $id)
    {
        $sapeurId = $request->attributes->get('sapeurId', []);
        if (!$sapeurId) {
            return response()->json(['error' => ['message' => 'Permissions insuffisantes']], 200);
        }

        $travail = $this->service->supprimer($id, $sapeurId);
        return response()->json(['data' => $travail]);
    }
}
