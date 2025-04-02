<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use App\Domaine\API\PaiementService;
use Illuminate\Http\Request;

class MesDecomptesController extends Controller
{
    protected $mesInfosService;
    protected $paiementService;

    public function __construct(MesInfosService $mesInfosService, PaiementService $paiementService)
    {
        $this->mesInfosService = $mesInfosService;
        $this->paiementService = $paiementService;
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function index(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->mesInfosService->mesDecomptes($sapeurId, $exerciceComptableId);
        return response()->json(['data' => $data]);
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function print(Request $request, $decompteId)
    {
        $sisKey = $request->header('Sis-Key', Null);
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        return $this->mesInfosService->printDecompte($sapeurId, $decompteId, $sisKey);
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function certificatSalaire(Request $request, $exerciceComptableId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        return $this->paiementService->certificatSalaireSapeur($exerciceComptableId, $sapeurId);
    }
}
