<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
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
            'articles.*.achat' => 'string|nullable',
            'articles.*.sapeur_id' => 'integer|nullable|exists:sapeurs,id',
            'articles.*.attribution' => 'date|nullable|required_with:articles.*.sapeur_id',
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
            'articles.*.emplacement.statut' => 'boolean|nullable',
        ]);

        $materiels = ArticleBusiness::creerArticles($data['articles']);
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
            'articles.*.sapeur_id' => 'integer|nullable|exists:sapeurs,id',
            'articles.*.attribution' => 'date|nullable|required_with:articles.*.sapeur_id',
            'articles.*.emplacement_id' => 'integer|nullable|exists:emplacements,id',
            'articles.*.compartiment' => 'string|nullable',
            'articles.*.chassis' => 'string|nullable',
            'articles.*.designation' => 'string|nullable',
            'articles.*.immatriculation' => 'string|nullable',
            'articles.*.date_peremption' => 'date_format:Y-m-d|nullable',
            'articles.*.statut' => 'required|boolean',
            'articles.*.emplacement.couleur_id' => 'integer|nullable',
            'articles.*.emplacement.parent_id' => 'integer|nullable',
            'articles.*.emplacement.est_etiquete' => 'boolean|nullable',
            'articles.*.emplacement.est_compartimentable' => 'boolean|nullable',
            'articles.*.emplacement.statut' => 'boolean|nullable',
        ]);

        $articles = ArticleBusiness::editArticles($data['articles']);
        return response()->json(['data' => $articles]);
    }

    /**
     * Supprimer des articles
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'articleIds' => 'required|array',
            'articleIds.*' => 'required|integer',
        ]);

        $articles = ArticleBusiness::deleteArticles($data['articleIds']);
        return response()->json(null, 204);
    }
}
