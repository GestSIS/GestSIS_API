<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;

class MesInterventionsController extends Controller
{

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesInterventions($sapeurId, $exerciceComptableId);
        return response()->json(['data' => $data]);
    }
}
