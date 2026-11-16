<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\AttributionBusiness;
use App\Models\Article;
use App\Models\Sapeur;
use Illuminate\Http\JsonResponse;

class AttributionController extends Controller
{
    /**
     * Historique des attributions d'un article (plus récente en premier).
     */
    public function indexPourArticle(int $articleId): JsonResponse
    {
        Article::findOrFail($articleId);

        return response()->json(['data' => AttributionBusiness::getAttributionsPourArticle($articleId)]);
    }

    /**
     * Historique des attributions d'un sapeur (plus récente en premier).
     */
    public function indexPourSapeur(int $sapeurId): JsonResponse
    {
        Sapeur::findOrFail($sapeurId);

        return response()->json(['data' => AttributionBusiness::getAttributionsPourSapeur($sapeurId)]);
    }
}
