<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\PaiementService;
use App\Infrastructure\Models\Ecriture;
use App\Infrastructure\Models\Paiement;
use Illuminate\Http\Request;

class MesDecomptesController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementService $paiementService)
    {
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

        $paiements = Paiement::where('sapeur_id', '=', $sapeurId)
            ->join('decomptes', 'paiements.decompte_id', '=', 'decomptes.id')
            ->where('decomptes.exercice_comptable_id', '=', $exerciceComptableId)
            ->select('paiements.*', 'decomptes.date as date', 'decomptes.designation as decompte')->get();
        $ecritures = Ecriture::where('sapeur_id', '=', $sapeurId)->whereNotNull('decompte_id')->get();

        $data = [
            'paiements' => $paiements,
            'ecritures' => $ecritures,
        ];
        return response()->json(['data' => $data]);
    }

    public function printResumeAnnuel(Request $request, $exerciceComptableId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        return PaiementService::impressionResumePourSapeur($exerciceComptableId, $sapeurId, $sisKey);
    }

    /**
     * Récupération des décomptes du sapeur
     */
    public function print(Request $request, $decompteId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        return PaiementService::impressionDecompteSapeur($decompteId, $sapeurId, $sisKey);
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
