<?php

namespace App\Application\Http\Controllers;

use App\Application\Auth\TokenTools;
use App\Domaine\API\ExerciceService;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ExerciceSapeur;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ExcuseController extends Controller
{

    protected $service;

    public function __construct(ExerciceService $service)
    {
        $this->service = $service;
    }

    public function downloadJustificatif(int $exerciceId, int $sapeurId)
    {
        $presence = ExerciceSapeur::where([['exercice_id', '=', $exerciceId], ['sapeur_id', '=', $sapeurId]])->first();
        if ($presence == null || !$presence->justificatif_filename) {
            throw new ArrayException([], "Aucun justificatif !");
        }

        $justificatif = ['path' => $presence->justificatif_path, 'filename' => $presence->justificatif_filename];

        $headers = array(
            'Content-Type: application/pdf',
            'Cache-Control: no-cache private',
            'Content-Description: File Transfer',
            'Content-Transfer-Encoding: binary'
        );
        return Storage::download($justificatif['path'], $justificatif['filename'], $headers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $convocationId)
    {
        $request->merge([
            'excuse_type_id' => (int) $request->get('excuse_type_id'),
            'excuse_statut' => (int) $request->get('excuse_statut'),
        ]);

        $data = $request->validate([
            'excuse_type_id' => 'nullable|integer',
            'remarque' => 'nullable|string|max:1000',
            // 'justificatif_filename' => 'required|boolean', // Nom du fichier
            // 'justificatif_path' => 'required|boolean', // Nom du fichier

            'excuse_statut' => 'integer',
            'justification' => 'nullable|string',
        ]);


        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif_file invalide']);
        }

        $file = $request->file('justificatif_file');

        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $presences = $this->service->updateExcuse($convocationId, $data, $file, $hasValidationPermission, $sisKey);

        return response()->json(['data' => $presences]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     */
    public function destroy(Request $request, int $exerciceId, int $sapeurId)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $presence = $this->service->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);

        return response()->json(['data' => $presence]);
    }
}
