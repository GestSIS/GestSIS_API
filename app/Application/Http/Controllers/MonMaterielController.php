<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use App\Domaine\Business\Materiel\ArticleBusiness;
use Illuminate\Http\Request;

class MonMaterielController extends Controller
{
    private $service = null;

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * Récupère les informations du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = ArticleBusiness::getArticlesPourSapeur($sapeurId);
        return response()->json(['data' => $data]);
    }
}
