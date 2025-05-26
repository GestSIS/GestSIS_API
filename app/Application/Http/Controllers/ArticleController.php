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
        $attribuable = $request->get('attribuable', false);
        if ($attribuable) {
            $articles = ArticleBusiness::getItemsAttribuable();
        } else {
            $articles = ArticleBusiness::getAllItems();
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
            'articles.*.sapeur_id' => 'required_if:emplacement_id,null',
            'articles.*.attribution' => 'required_unless:sapeur_id,null',
            'articles.*.emplacement_id' => 'required_if:sapeur_id,null',
            'articles.*.compartiment' => 'string|nullable',
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
            'articles.*.taille' => 'string|nullable',
            'articles.*.remarque' => 'string|nullable',
            'articles.*.attribution' => 'date|nullable',
            'articles.*.materiel' => 'required|array',
            'articles.*.materiel.id' => 'required|integer',
            'articles.*.materiel.quantite' => 'integer|nullable',
            'articles.*.materiel.numero' => 'string|nullable',
            'articles.*.materiel.achat' => 'string|nullable',
        ]);

        $articles = $this->service->update($data['articles']);
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
