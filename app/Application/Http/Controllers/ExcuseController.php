<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ExerciceSapeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExcuseController extends Controller
{
    protected $business;

    public function __construct(ExerciceBusiness $business)
    {
        $this->business = $business;
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

    public function store(Request $request, int $convocationId)
    {
        $request->merge([
            'excuse_type_id' => (int) $request->get('excuse_type_id'),
            'excuse_statut' => (int) $request->get('excuse_statut'),
        ]);

        $data = $request->validate([
            'excuse_type_id' => 'nullable|integer',
            'remarque' => 'nullable|string|max:1000',
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

        $statut = $this->business->updateSapeurs($convocationId, $data, $hasValidationPermission);
        return response()->json(['data' => [
            'statut' => $statut,
            'sapeurs' => ExerciceBusiness::listeSapeurOfExerciceById($convocationId),
        ]]);
    }

    public function destroy(Request $request, int $exerciceId, int $sapeurId)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $presence = $this->business->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);

        return response()->json(['data' => $presence]);
    }
}
