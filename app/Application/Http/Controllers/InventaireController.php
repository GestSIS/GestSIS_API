<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\InventaireBusiness;
use App\Models\Inventaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventaireController extends Controller
{
    /**
     * Liste des inventaires, avec leur racine et leurs compteurs.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => InventaireBusiness::liste()]);
    }

    /**
     * Détail d'un inventaire : périmètre figé et lignes.
     */
    public function show(int $id): JsonResponse
    {
        $inventaire = Inventaire::with(['emplacements', 'lignes'])->findOrFail($id);

        return response()->json(['data' => $inventaire]);
    }

    /**
     * Démarre un inventaire sur un emplacement et ses sous-emplacements actifs.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'emplacement_id' => 'required|integer|exists:emplacements,id',
            'designation' => 'required|string|max:255',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $inventaire = InventaireBusiness::demarrer(
            $data['emplacement_id'],
            $data['designation'],
            $data['date'],
            $request->attributes->get('sapeurId'),
        );

        return response()->json(['data' => $inventaire], 201);
    }

    /**
     * Valide un inventaire dont toutes les pièces ont été vérifiées.
     */
    public function valider(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'remarque' => 'nullable|string|max:5000',
        ]);

        $inventaire = InventaireBusiness::valider($id, $data['remarque'] ?? null, $request->attributes->get('sapeurId'));

        return response()->json(['data' => $inventaire]);
    }

    /**
     * Supprime un inventaire en cours, avec sa saisie.
     */
    public function destroy(int $id): JsonResponse
    {
        InventaireBusiness::supprimer($id);

        return response()->json(null, 204);
    }
}
