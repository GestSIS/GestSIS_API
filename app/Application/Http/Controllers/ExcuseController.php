<?php

namespace App\Application\Http\Controllers;

use App\Application\Auth\TokenTools;
use App\Domaine\API\ExerciceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controller pour la convocation de sapeurs à des exercices
 * TODO: Fusionner avec ConvocationController
 */
class ConvocationsController extends Controller
{

    protected $service;

    public function __construct(ExerciceService $service)
    {
        $this->service = $service;
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
            'amende' => (bool) $request->get('amende'),
            'excuse_type_id' => (int) $request->get('excuse_type_id'),
            'excuse_statut' => (int) $request->get('excuse_statut'),
        ]);

        $data = $request->validate([
            'excuse_type_id' => 'nullable|integer',
            'remarque' => 'nullable|string',
            // 'justificatif_filename' => 'required|boolean', // Nom du fichier
            // 'justificatif_path' => 'required|boolean', // Nom du fichier

            'excuse_statut' => 'integer',
            'justification' => 'nullable|string',
            'amende' => 'boolean',
        ]);


        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif_file invalide']);
        }

        $file = $request->file('justificatif_file');

        $sisKey = $request->header('Sis-Id', Null);
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
    public function destroy(int $convocationId)
    {
        $statut = $this->service->removeExcuse($convocationId);

        return response()->json(['data' => $statut]);
    }
}
