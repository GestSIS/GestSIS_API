<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\InventaireEcartBusiness;
use App\Models\Inventaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Traitement des écarts d'un inventaire validé par le responsable matériel.
 */
class InventaireEcartController extends Controller
{
    /**
     * Écarts d'un inventaire validé, comparés au stock actuel (vide pour un inventaire en cours).
     */
    public function index(int $inventaireId): JsonResponse
    {
        $inventaire = Inventaire::findOrFail($inventaireId);

        return response()->json(['data' => $inventaire->statut === 'VALIDE' ? InventaireEcartBusiness::ecarts($inventaire) : []]);
    }

    /**
     * Applique une décision à un écart et renvoie les écarts recalculés.
     */
    public function traiter(Request $request, int $inventaireId): JsonResponse
    {
        $data = $request->validate([
            'ecart' => 'required|string|max:100',
            'signature' => 'required|string|max:100',
            'decision' => ['required', 'string', Rule::in(InventaireEcartBusiness::DECISIONS)],
            'remarque' => 'nullable|string|max:1000',
            'materiel_type_id' => 'nullable|integer|exists:materiel_types,id',
            'article' => 'sometimes|array',
            'article.designation' => 'nullable|string|max:255',
            'article.taille' => 'nullable|string|max:255',
            'article.date_peremption' => 'nullable|date',
            'article.remarque' => 'nullable|string|max:1000',
            'article.achat' => 'nullable|string|max:255',
            'article.compartiment' => 'nullable|string|max:255',
            'article.est_etiquete' => 'nullable|boolean',
            'article.emplacement_id' => 'nullable|integer|exists:emplacements,id',
            'article.numero' => 'nullable|string|max:255',
            'article.disponible_intervention' => 'nullable|boolean',
            // Configuration pièce par pièce, dans l'ordre des pièces à créer
            'articles' => 'sometimes|array|list',
            'articles.*' => 'array',
            'articles.*.numero' => 'nullable|string|max:255',
            'articles.*.emplacement_id' => 'nullable|integer|exists:emplacements,id',
            'articles.*.compartiment' => 'nullable|string|max:255',
            'articles.*.taille' => 'nullable|string|max:255',
            'articles.*.date_peremption' => 'nullable|date',
            'articles.*.est_etiquete' => 'nullable|boolean',
            'articles.*.achat' => 'nullable|string|max:255',
            'articles.*.remarque' => 'nullable|string|max:1000',
        ]);

        InventaireEcartBusiness::traiter(
            $inventaireId,
            $data['ecart'],
            $data['signature'],
            $data['decision'],
            $data['remarque'] ?? null,
            $data['materiel_type_id'] ?? null,
            $data['article'] ?? [],
            $data['articles'] ?? [],
            $request->attributes->get('sapeurId'),
        );

        return $this->index($inventaireId);
    }
}
