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

    public function index($exerciceComptableId)
    {
        $travaux = $this->service->travaux($exerciceComptableId);

        return response()->json(['data' => $travaux]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'travaux' => 'array',
            'travaux.*.designation' => 'required|string|min:1',
            'travaux.*.date' => 'required|date',
            'travaux.*.sapeur_id' => 'required|integer|exist:sapeurs,id',
            'travaux.*.travail_type_id' => 'required|integer|exist:travail_types,id',
            'travaux.*.exercice_comptable_id' => 'required|integer|exist:exercice_comptables,id',
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
            'travail_type_id' => 'required|integer|exist:travail_types,id',
        ]);

        $sapeurId = $request->attributes->get('sapeurId', []);

        $travail = $this->service->modifier($travailId, $data, $sapeurId);
        return response()->json(['data' => $travail]);
    }

    public function review(Request $request, $travailId)
    {
        $data = $request->validate([
            'justification' => 'integer|string',
            'accepte' => 'boolean|required',
            'quantite' => 'decimal|nullable',
        ]);

        $travail = $this->service->modifier($travailId, $data['accepte'], $data['justification'], $data['quantite'] ?? null);
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
