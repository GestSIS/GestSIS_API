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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $exerciceId)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasPresencePermission = $admin || in_array('exercice.presence', $perms);
        $sapeurs = $this->service->listeSapeurOfExerciceById($exerciceId, $hasPresencePermission);

        return response()->json(['data' => $sapeurs]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, int $exerciceId)
    {

        $data = $request->validate([
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.absent' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.amende' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',

            // Actuellement impossible d'ajouter des heures supp directement à l'ajout d'un sapeur
            // 'sapeurs.*.heures.*.quantite' => 'required|nullable|numeric',
            // 'sapeurs.*.heures.*.heure_exercice_type_id' => 'required|integer',
        ]);

        $sapeur = $this->service->addSapeurs($exerciceId, $data['sapeurs']);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update une présence d'un exercice
     *
     * @return Response
     */
    public function updatePresence(Request $request, $id)
    {
        $request->merge([
            'id' => (int) $request->get('id'),
            'convoque' => (int) $request->get('convoque'),
            'present' => (int) $request->get('present'),
            'absent' => (int) $request->get('absent'),
            'remplace' => (int) $request->get('remplace'),
            'excuse_type_id' => (int) $request->get('excuse_type_id'),
            'excuse_statut' => (int) $request->get('excuse_statut'),
        ]);

        $data = $request->validate([
            'convoque' => 'required|integer',
            'present' => 'required|integer',
            'absent' => 'required|integer',
            'remplace' => 'required|integer',

            // Auto
            // 'date_demande' => 'required|boolean',
            // 'date_validation' => 'required|boolean',

            'excuse_type_id' => 'nullable|integer',
            'remarque' => 'nullable|string|max:1000',
            // 'justificatif_filename' => 'required|boolean', // Nom du fichier
            // 'justificatif_path' => 'required|boolean', // Nom du fichier

            'excuse_statut' => 'integer',
            'justification' => 'nullable|string|max:1000',
        ]);


        if ($request->hasFile('justificatif_file') && !$request->file('justificatif_file')->isValid()) {
            return response()->json(['error' => 'Fichier justificatif_file invalide']);
        }

        $file = $request->file('justificatif_file');

        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $presences = $this->service->updatePresence($id, $data, $file, $hasValidationPermission, $sisKey);

        return response()->json(['data' => $presences]);
    }

    /**
     * Update the specified resource in storage.
     * WARNING GestSIS Mobile
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function updatePresences(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*.id' => 'nullable|integer',
            'sapeurs.*.sapeur_id' => 'integer|required',
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.absent' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
            'sapeurs.*.heures.*.id' => 'nullable|integer',
            'sapeurs.*.heures.*.quantite' => 'nullable|numeric',
            'sapeurs.*.heures.*.heure_exercice_type_id' => 'nullable|integer',
        ]);

        $sapeur = $this->service->updatePresences($exerciceId, $data['sapeurs']);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     */
    public function destroy(Request $request, int $exerciceId)
    {

        $data = $request->validate([
            'sapeurs.*' => 'integer'
        ]);

        $statut = $this->service->removeSapeurs($exerciceId, $data['sapeurs']);

        return response()->json(['data' => $statut]);
    }

    /**
     * Update les présences sans tenir compte d'un exercice en particulier
     *
     * @return Response
     */
    public function createHeure(Request $request)
    {
        $data = $request->validate([
            'exercice_id' => 'integer|required',
            'sapeur_id' => 'integer|required',
            'quantite' => 'nullable|numeric',
            'heure_exercice_type_id' => 'nullable|integer',
        ]);

        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $heure = $this->service->createHeure($data, $hasValidationPermission);

        return response()->json(['data' => $heure]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function updateHeure(Request $request, int $heureId)
    {
        // TODO: à implémenter
        $data = $request->validate([
            'id' => 'integer|required',
            'quantite' => 'nullable|numeric',
        ]);

        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $heure = $this->service->updateHeure($heureId, $data, $hasValidationPermission);

        return response()->json(['data' => $heure]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     */
    public function destroyHeure(Request $request, int $heureId)
    {
        $admin = $request->attributes->get('admin');
        $perms = $request->attributes->get('permissions', []);
        $hasValidationPermission = $admin || in_array('exercice.validation', $perms);
        $statut = $this->service->removeHeure($heureId, $hasValidationPermission);

        return response()->json(['data' => $statut]);
    }

    /**
     * Annule la présence de sapeurs
     *
     * @param Request $request
     * @param int $sapeurId
     * @return Response
     */
    public function supprimerConvocations(Request $request, int $sapeurId)
    {
        $data = $request->validate([
            'convocations' => 'array|required',
            'convocations.*' => 'integer'
        ]);

        $statut = $this->service->supprimerConvocations($sapeurId, $data['convocations']);

        return response()->json(['data' => $statut]);
    }
}
