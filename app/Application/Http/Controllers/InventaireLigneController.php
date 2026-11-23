<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\InventaireSaisieBusiness;
use App\Models\Article;
use App\Models\Inventaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

/**
 * Saisie d'un inventaire en cours. Chaque action renvoie l'inventaire complet
 * (périmètre et lignes) pour que l'écran de saisie reste à jour.
 */
class InventaireLigneController extends Controller
{
    /**
     * Matériel trouvé en plus dans un nœud de l'inventaire.
     */
    public function store(Request $request, int $inventaireId): JsonResponse
    {
        $data = $request->validate([
            'inventaire_emplacement_id' => 'required|integer',
            'numero' => 'nullable|string|max:255',
            'materiel_type_id' => 'nullable|integer|exists:materiel_types,id',
            'nouveau_type' => 'nullable|string|max:255',
            'quantite' => 'nullable|integer|min:1|max:1000',
            'compartiment' => 'nullable|string|max:255',
            'taille' => 'nullable|string|max:255',
            'date_peremption' => 'nullable|date',
            'est_etiquete' => 'nullable|boolean',
            'remarque_sapeur' => 'nullable|string|max:1000',
        ]);

        InventaireSaisieBusiness::ajouterEnPlus(
            $inventaireId,
            $data['inventaire_emplacement_id'],
            $data['numero'] ?? null,
            $data['materiel_type_id'] ?? null,
            $data['nouveau_type'] ?? null,
            $data['quantite'] ?? 1,
            Arr::only($data, ['compartiment', 'taille', 'date_peremption', 'est_etiquete', 'remarque_sapeur']),
            $request->attributes->get('sapeurId'),
        );

        return $this->detail($inventaireId, 201);
    }

    /**
     * Article en service portant ce numéro, pour annoncer ce que donnera son ajout en plus
     * (null si le numéro est inconnu). Le sapeur qui inventorie n'a pas forcément accès à
     * la liste des articles.
     */
    public function articleParNumero(Request $request, int $inventaireId): JsonResponse
    {
        $data = $request->validate(['numero' => 'required|string|max:255']);
        Inventaire::findOrFail($inventaireId);

        $article = Article::nonSupprimes()->where('numero', trim($data['numero']))->with('materielType')->first();

        return response()->json(['data' => $article === null ? null : [
            'id' => $article->id,
            'numero' => $article->numero,
            'designation' => InventaireSaisieBusiness::designationArticle($article),
            'emplacement_id' => $article->emplacement_id,
            'attribue' => $article->sapeur_id !== null,
        ]]);
    }

    /**
     * Statut (null = à vérifier) d'une pièce et/ou précision d'une pièce ou de son lot.
     */
    public function update(Request $request, int $inventaireId, int $ligneId): JsonResponse
    {
        $data = $request->validate([
            'statut' => 'sometimes|nullable|string|in:PRESENT,ABSENT,DEFECTUEUX',
            'remarque_sapeur' => 'sometimes|nullable|string|max:1000',
        ]);

        if (array_key_exists('statut', $data)) {
            InventaireSaisieBusiness::modifierStatut($inventaireId, $ligneId, $data['statut'], $request->attributes->get('sapeurId'));
        }
        if (array_key_exists('remarque_sapeur', $data)) {
            InventaireSaisieBusiness::modifierRemarque($inventaireId, $ligneId, $data['remarque_sapeur']);
        }

        return $this->detail($inventaireId);
    }

    /**
     * Retire une ligne de matériel en plus.
     */
    public function destroy(int $inventaireId, int $ligneId): JsonResponse
    {
        InventaireSaisieBusiness::retirer($inventaireId, $ligneId);

        return $this->detail($inventaireId);
    }

    /**
     * Quantité constatée du lot auquel appartient la ligne (null = à vérifier), dont les
     * pièces défectueuses (absent = garder le nombre saisi).
     */
    public function compterLot(Request $request, int $inventaireId, int $ligneId): JsonResponse
    {
        $data = $request->validate([
            'quantite' => 'present|nullable|integer|min:0|max:1000',
            'defectueux' => 'nullable|integer|min:0|max:1000',
        ]);

        InventaireSaisieBusiness::compterLot(
            $inventaireId,
            $ligneId,
            $data['quantite'],
            $data['defectueux'] ?? null,
            $request->attributes->get('sapeurId'),
        );

        return $this->detail($inventaireId);
    }

    /**
     * « Reste conforme » : les pièces encore à vérifier du nœud deviennent présentes.
     */
    public function marquerConformes(Request $request, int $inventaireId, int $noeudId): JsonResponse
    {
        InventaireSaisieBusiness::marquerConformes($inventaireId, $noeudId, $request->attributes->get('sapeurId'));

        return $this->detail($inventaireId);
    }

    private function detail(int $inventaireId, int $status = 200): JsonResponse
    {
        return response()->json(['data' => Inventaire::with(['emplacements', 'lignes'])->findOrFail($inventaireId)], $status);
    }
}
