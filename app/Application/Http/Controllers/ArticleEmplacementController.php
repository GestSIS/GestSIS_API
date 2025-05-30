<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleEmplacementController extends Controller
{
    protected $service;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $articles = ArticleBusiness::getArticlesParEmplacement($sapeurId);

        return response()->json(['data' => $articles]);
    }

    /**
     * Attribution de matériel à un emplacement, équivalent à du retour de matériel
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, int $emplacementId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'articleIds' => 'required|array',
            'articleIds.*' => 'integer|min:1',
        ]);

        $articles = ArticleBusiness::retourArticles($emplacementId, $data['date'], $data['articleIds']);

        return response()->json(['data' => $articles]);
    }
}
