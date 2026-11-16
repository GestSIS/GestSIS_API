<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleSapeurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $articles = ArticleBusiness::getArticlesPourSapeur($sapeurId);

        return response()->json(['data' => $articles]);
    }

    /**
     * Attribution de matériel à un sapeur (transfert si l'article est déjà attribué)
     */
    public function store(Request $request, int $sapeurId): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'articleIds' => 'required|array',
            'articleIds.*' => 'integer|min:1',
        ]);

        $articles = ArticleBusiness::attribuerArticles(
            $sapeurId,
            $data['date'],
            $data['articleIds'],
            $request->attributes->get('sapeurId'),
        );

        return response()->json(['data' => $articles], 201);
    }

    /**
     * Retour de matériel
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'emplacementId' => 'integer|min:1|required',
            'articleIds' => 'array|required',
            'articleIds.*' => 'integer|min:1',
        ]);

        $articles = ArticleBusiness::retourArticles(
            $data['emplacementId'],
            $data['date'],
            $data['articleIds'],
            $request->attributes->get('sapeurId'),
        );

        return response()->json(null, 204);
    }

    public function pdf(Request $request, int $sapeurId)
    {
        $sisKey = $request->header('Sis-Key');
        return ArticleBusiness::pdfArticlePourSapeur($sapeurId, $sisKey);
    }
}
