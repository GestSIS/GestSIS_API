<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\ControleMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MesControlesMedicauxController extends Controller
{
    /**
     * Récupération des exercices du sapeur
     */
    public function index(Request $request)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $data = ControleMedical::where('sapeur_id', $sapeurId)->get();
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

        $controle = ControleMedical::find($controleMedicalId);
        if (!$controle || $sapeurId !== $controle->sapeur_id) {
            throw new ArrayException([], 'Accès refusé');
        }
        $justificatif = ControleMedicalBusiness::getJustificatif($controleMedicalId);

        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($justificatif['path'], $justificatif['filename'], $headers);
    }
}
