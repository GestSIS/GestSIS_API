<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $attribuable = $request->input('attribuable', false);
        if ($attribuable) {
            $articles = ArticleBusiness::getArticlesAttribuable();
        } else {
            $articles = ArticleBusiness::getAllArticles();
        }

        return response()->json(['data' => $articles]);
    }

    public function aRecuperer()
    {
        return response()->json(['data' => ArticleBusiness::getArticlesARecuperer()]);
    }

    /**
     * Créer un ou des articles
     * 
     * @param int $exerciceId - id de l'exercice
     * @param date $date - date de la création du décompte
     * @param boolean $deduction - true si les déduction doivent être faites sur ce paiement
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'articles' => 'required|array|min:1',
            'articles.*.taille' => 'string|nullable',
            'articles.*.remarque' => 'string|nullable',
            'articles.*.materiel_type_id' => 'required|integer|exists:materiel_types,id',
            // Un type non numéroté crée une ligne par pièce : la quantité est bornée
            // pour éviter de créer par erreur des milliers d'articles d'un coup.
            'articles.*.quantite' => 'integer|min:1|max:50|nullable',
            'articles.*.numero' => 'string|nullable',
            'articles.*.est_etiquete' => 'boolean',
            'articles.*.disponible_intervention' => 'boolean',
            'articles.*.achat' => 'string|nullable',
            'articles.*.sapeur_id' => 'integer|nullable|exists:sapeurs,id',
            'articles.*.attribution' => 'date|nullable|before_or_equal:today|required_with:articles.*.sapeur_id',
            'articles.*.emplacement_id' => 'integer|nullable|exists:emplacements,id',
            'articles.*.compartiment' => 'string|nullable',
            'articles.*.chassis' => 'string|nullable',
            'articles.*.designation' => 'string|nullable',
            'articles.*.immatriculation' => 'string|nullable',
            'articles.*.date_peremption' => 'date_format:Y-m-d|nullable',
            'articles.*.emplacement.couleur_id' => 'integer|nullable',
            'articles.*.emplacement.parent_id' => 'integer|nullable',
            'articles.*.emplacement.est_etiquete' => 'boolean|nullable',
            'articles.*.emplacement.est_compartimentable' => 'boolean|nullable',
        ]);

        $materiels = ArticleBusiness::creerArticles($data['articles'], $request->attributes->get('sapeurId'));
        return response()->json(['data' => $materiels]);
    }

    /**
     * Modifier du matériel
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'articles' => 'required|array|min:1',
            'articles.*.id' => 'required|integer|exists:articles,id',
            'articles.*.materiel_type_id' => 'integer|nullable|exists:materiel_types,id',
            'articles.*.taille' => 'string|nullable',
            'articles.*.remarque' => 'string|nullable',
            'articles.*.numero' => 'string|nullable',
            'articles.*.est_etiquete' => 'boolean',
            'articles.*.achat' => 'string|nullable',
            'articles.*.emplacement_id' => 'integer|nullable|exists:emplacements,id',
            'articles.*.compartiment' => 'string|nullable',
            'articles.*.chassis' => 'string|nullable',
            'articles.*.designation' => 'string|nullable',
            'articles.*.immatriculation' => 'string|nullable',
            'articles.*.date_peremption' => 'date_format:Y-m-d|nullable',
            'articles.*.disponible_intervention' => 'boolean',
            'articles.*.emplacement.couleur_id' => 'integer|nullable',
            'articles.*.emplacement.parent_id' => 'integer|nullable',
            'articles.*.emplacement.est_etiquete' => 'boolean|nullable',
            'articles.*.emplacement.est_compartimentable' => 'boolean|nullable',
        ]);

        $articles = ArticleBusiness::editArticles($data['articles']);
        return response()->json(['data' => $articles]);
    }

    /**
     * Suppression logique d'articles (date, auteur et remarque enregistrés).
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'articleIds' => 'required|array|min:1',
            'articleIds.*' => 'required|integer|exists:articles,id',
            'date' => 'required|date|before_or_equal:today',
            'remarque' => 'nullable|string',
        ]);

        ArticleBusiness::supprimerArticles(
            $data['articleIds'],
            $data['date'],
            $data['remarque'] ?? null,
            $request->attributes->get('sapeurId'),
        );
        return response()->json(null, 204);
    }

    /**
     * Suppression logique d'une quantité de pièces d'un lot non numéroté ;
     * le serveur choisit les pièces.
     */
    public function supprimerLot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'materiel_type_id' => 'required|integer|exists:materiel_types,id',
            'emplacement_id' => 'required|integer|exists:emplacements,id',
            'compartiment' => 'nullable|string',
            'quantite' => 'required|integer|min:1',
            'date' => 'required|date|before_or_equal:today',
            'remarque' => 'nullable|string',
        ]);

        $articles = ArticleBusiness::supprimerLot(
            $data['materiel_type_id'],
            $data['emplacement_id'],
            $data['compartiment'] ?? '',
            $data['quantite'],
            $data['date'],
            $data['remarque'] ?? null,
            $request->attributes->get('sapeurId'),
        );
        return response()->json(['data' => $articles]);
    }

    /**
     * Restauration d'articles supprimés, rangés dans l'emplacement donné
     * (un véhicule réactive son propre emplacement).
     */
    public function restaurer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'articleIds' => 'required|array|min:1',
            'articleIds.*' => 'required|integer|exists:articles,id',
            'emplacement_id' => 'required|integer|exists:emplacements,id',
        ]);

        $articles = ArticleBusiness::restaurerArticles($data['articleIds'], $data['emplacement_id']);
        return response()->json(['data' => $articles]);
    }
}
