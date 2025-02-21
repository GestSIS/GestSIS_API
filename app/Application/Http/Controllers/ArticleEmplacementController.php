<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\Materiel\ArticleBusiness;
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
        $articles = ArticleBusiness::getItemsByLocation($sapeurId);

        return response()->json(['data' => $articles]);
    }
}
