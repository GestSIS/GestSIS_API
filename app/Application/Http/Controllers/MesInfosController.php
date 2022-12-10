<?php

namespace App\Application\Http\Controllers;

use Illuminate\Http\Request;

use App\Domaine\API\SapeurService;

class MesInfosController extends Controller
{

    public function __construct(SapeurService $service)
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

        $data = $this->service->getSapeurDetailsById($sapeurId);
        return response()->json(['data' => $data]);
    }
}
