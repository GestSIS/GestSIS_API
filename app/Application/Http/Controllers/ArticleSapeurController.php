<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
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
     * Attribution de matériel à un sapeur
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'articleIds' => 'required|array',
            'articleIds.*' => 'integer|min:1',
        ]);

        $articles = ArticleBusiness::attribuerArticles($sapeurId, $data['date'], $data['articleIds']);

        return response()->json(['data' => $articles]);
    }

    /**
     * Retour de matériel
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|required',
            'emplacementId' => 'integer|min:1|required',
            'articleIds' => 'array|required',
            'articleIds.*' => 'integer|min:1',
        ]);

        $articles = ArticleBusiness::retourArticles($data['emplacementId'], $data['date'], $data['articleIds']);

        return response()->json(['data' => $articles]);
    }

}
