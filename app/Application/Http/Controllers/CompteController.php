<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteService;
use Illuminate\Http\Response;

class CompteController extends Controller
{
    protected $service;

    public function __construct(ComptabiliteService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json(['data' => $this->service->getComptes()]);
    }

    public function ecritures(int $compteId, int $exerciceComptableId)
    {
        return response()->json(['data' => $this->service->getEcrituresByCompte($compteId, $exerciceComptableId)]);
    }

    public function generatePdf(int $exerciceComptableId)
    {
        return $this->service->decompteAnnuelParSapeur($exerciceComptableId);
    }
}
