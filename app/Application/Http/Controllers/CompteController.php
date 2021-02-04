<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use App\Domaine\API\ComptabiliteService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompteController extends Controller
{
    protected $paramService;

    public function __construct(ComptabiliteService $service, ComptabiliteParamService $paramService)
    {
        $this->service = $service;
        $this->paramService = $paramService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json(['data' => $this->paramService->comptes()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'numero' => 'numeric',
            'actif' => 'boolean',
        ]);

        $compte = $this->paramService->ajouterCompte($data);
        return response()->json(['data' => $compte]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1',
            'numero' => 'numeric',
            'actif' => 'boolean',
        ]);

        $compte = $this->paramService->modifierCompte($id, $data);
        return response()->json(['data' => $compte]);
    }

    public function destroy($id)
    {
        $compte = $this->paramService->supprimerCompte($id);
        return response()->json(['data' => $compte]);
    }

    public function ecritures(int $compteId, int $exerciceComptableId)
    {
        return response()->json(['data' => $this->service->getEcrituresByCompte($compteId, $exerciceComptableId)]);
    }

    public function generatePdf(int $exerciceComptableId)
    {
        // TODO: Really in this controller ?
        return $this->service->decompteAnnuelParSapeur($exerciceComptableId);
    }
}
