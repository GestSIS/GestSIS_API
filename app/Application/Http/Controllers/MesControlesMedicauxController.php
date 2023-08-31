<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MesControlesMedicauxController extends Controller
{
    private $service = null;

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = $this->service->mesControlesMedicaux($sapeurId);
        return response()->json(['data' => $data]);
    }

    /**
     * Récupération des exercices du sapeur
     */
    public function justificatif(Request $request, int $controleMedicalId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $justificatif = $this->service->monJustificatifMedical($sapeurId, $controleMedicalId);

        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($justificatif['path'], $justificatif['filename'], $headers);
    }
}
