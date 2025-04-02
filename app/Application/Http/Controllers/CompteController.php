<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\ComptabiliteParamService;
use App\Domaine\API\ImputationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompteController extends Controller
{
    protected $paramService;
    protected $service;

    public function __construct(ImputationService $service, ComptabiliteParamService $paramService)
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
            'designation' => 'string|min:1|required',
            'numero' => 'string|required',
            'produit' => 'boolean|required',
        ]);

        $compte = $this->paramService->ajouterCompte($data);
        return response()->json(['data' => $compte]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'designation' => 'string|min:1|required',
            'numero' => 'string|required',
            'produit' => 'boolean|required',
        ]);

        $compte = $this->paramService->modifierCompte($id, $data);
        return response()->json(['data' => $compte]);
    }

    public function destroy($id)
    {
        $this->paramService->supprimerCompte($id);
        return response()->json(['data' => 'ok']);
    }

    public function ecritures(int $id, int $exerciceComptableId)
    {
        return response()->json(['data' => $this->service->getEcrituresByCompte($id, $exerciceComptableId)]);
    }

    public function justificatifIndividuel(Request $request, int $exerciceComptableId, int $compteId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return $this->service->justificatifIndividuel($exerciceComptableId, $compteId, $sisKey);
    }

    public function justificatifComplet(Request $request, int $exerciceComptableId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return $this->service->justificatifComplet($exerciceComptableId, $sisKey);
    }
}
