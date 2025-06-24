<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\MesInfosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MesExcusesController extends Controller
{
    private $service = null;

    public function __construct(MesInfosService $service)
    {
        $this->service = $service;
    }

    /**
     * S'excuser à un exercice
     */
    public function store(Request $request, int $exerciceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $request->merge([
            'excuse_type_id' => (int) $request->get('excuse_type_id'),
            // 'remarque' => (string) $request->get('remarque'),
        ]);

        $data = $request->validate([
            'excuse_type_id' => 'required|integer|min:1',
            'remarque' => 'string|required|min:1|max:1000',
            // 'justificatif_filename' => 'required|boolean', // Nom du fichier
            // 'justificatif_path' => 'required|boolean', // Nom du fichier
        ]);

        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif invalide']);
        }

        $file = $request->file('justificatif_file');

        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));

        $data = $this->service->creerExcuse($sapeurId, $exerciceId, $data, $file, $sisKey);
        return response()->json(['data' => $data]);
    }

    /**
     * S'excuser à un exercice
     */
    public function delete(Request $request, int $exerciceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');
        if ($sapeurId === null || intval($sapeurId) <= 0) {
            return response()->json(['error' => 'Votre compte n\'est pas lié à un sapeur']);
        }

        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);

        $data = $this->service->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);
        return response()->json(['data' => $data]);
    }

    public function download(Request $request, int $exerciceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');

        $justificatif = $this->service->getJustificatif($exerciceId, $sapeurId);

        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($justificatif['path'], $justificatif['filename'], $headers);
    }
}
