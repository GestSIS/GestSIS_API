<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\ExerciceBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\ExerciceSapeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MesExcusesController extends Controller
{
    private $exerciceBusiness;

    public function __construct(ExerciceBusiness $exerciceBusiness)
    {
        $this->exerciceBusiness = $exerciceBusiness;
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
            'excuse_type_id' => (int) $request->input('excuse_type_id'),
        ]);

        $data = $request->validate([
            'excuse_type_id' => 'required|integer|min:1',
            'remarque' => 'string|required|min:1|max:1000',
        ]);

        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif invalide']);
        }

        $file = $request->file('justificatif_file');
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));

        $data = $this->exerciceBusiness->creerExcuse($sapeurId, $exerciceId, $data, $file, $sisKey);
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

        $data = $this->exerciceBusiness->removeExcuse($sapeurId, $exerciceId, $hasValidationPermission);
        return response()->json(['data' => $data]);
    }

    public function download(Request $request, int $exerciceId)
    {
        $sapeurId = $request->attributes->get('sapeurId');

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
}
