<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;

class MesDecomptesController extends Controller
{


    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesDecomptes($sapeurId, $exerciceComptableId);
        return response()->json(['data' => $data]);
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function print(Request $request, $decompteId)
    {
        $sapeurId = $request->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        return $this->service->printDecompte($sapeurId, $decompteId);
    }
}
