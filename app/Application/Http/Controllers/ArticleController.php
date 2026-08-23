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
        $lavable = $request->input('lavable', false);
        if ($attribuable) {
            $articles = ArticleBusiness::getArticlesAttribuable();
        } else if ($lavable) {
            $articles = ArticleBusiness::getArticlesLavable();
        } else {
            $articles = ArticleBusiness::getAllArticles();
        }

        return response()->json(['data' => $articles]);
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
            'articles.*.taille' => 'string|nullable',
            'articles.*.remarque' => 'string|nullable',
            'articles.*.materiel_type_id' => 'integer|nullable',
            'articles.*.quantite' => 'integer|nullable',
            'articles.*.numero' => 'string|nullable',
            'articles.*.est_etiquete' => 'boolean',
            'articles.*.achat' => 'string|nullable',
            'articles.*.sapeur_id' => 'integer|nullable',
            'articles.*.attribution' => 'required_unless:sapeur_id,null',
            'articles.*.emplacement_id' => 'integer|nullable',
            'articles.*.compartiment' => 'string|nullable',
            'articles.*.chassis' => 'string|nullable',
            'articles.*.designation' => 'string|nullable',
            'articles.*.immatriculation' => 'string|nullable',
            'articles.*.emplacement.couleur_id' => 'integer|nullable',
            'articles.*.emplacement.parent_id' => 'integer|nullable',
            'articles.*.emplacement.est_etiquete' => 'boolean|nullable',
            'articles.*.emplacement.est_compartimentable' => 'boolean|nullable',
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
            'articles.*.id' => 'required|integer',
            'articles.*.materiel_type_id' => 'integer|nullable',
            'articles.*.taille' => 'string|nullable',
            'articles.*.remarque' => 'string|nullable',
            'articles.*.numero' => 'string|nullable',
            'articles.*.est_etiquete' => 'boolean',
            'articles.*.achat' => 'string|nullable',
            'articles.*.sapeur_id' => 'integer|nullable',
            'articles.*.attribution' => 'required_unless:sapeur_id,null',
            'articles.*.emplacement_id' => 'integer|nullable',
            'articles.*.compartiment' => 'string|nullable',
            'articles.*.chassis' => 'string|nullable',
            'articles.*.designation' => 'string|nullable',
            'articles.*.immatriculation' => 'string|nullable',
            'articles.*.statut' => 'boolean|nullable',
            'articles.*.emplacement.couleur_id' => 'integer|nullable',
            'articles.*.emplacement.parent_id' => 'integer|nullable',
            'articles.*.emplacement.est_etiquete' => 'boolean|nullable',
            'articles.*.emplacement.est_compartimentable' => 'boolean|nullable',
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
        return response()->json(['data' => $articles]);
    }
}
