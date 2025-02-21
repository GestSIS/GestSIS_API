<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
use Illuminate\Http\Response;
use Request;

class ArticleSapeurController extends Controller
{
    protected $service;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(int $sapeurId)
    {
        $articles = ArticleBusiness::getItemsBySapeur($sapeurId);

        return response()->json(['data' => $articles]);
    }

    /**
     * Attribution de matériel à un sapeur
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'articleIds' => 'required|array',
            'articleIds.*' => 'integer|min:1',
        ]);

        $materiels = ArticleBusiness::attribuerArticles($sapeurId, $data['date'], $data['articleIds']);

        return response()->json(['data' => $materiels]);
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

        $articles = ArticleBusiness::retourArticles($data['date'], $data['articleIds'], $data['emplacementId']);

        return response()->json(['data' => $articles]);
    }

}
