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
    public function index($exerciceId)
    {
        $sapeurs = $this->service->listeSapeurOfExerciceById($exerciceId);

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
            'sapeurs.*.amende' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
            'sapeurs.*.sapeur_id' => 'required|integer|exists:sapeurs,id',
            // Actuellement impossible d'ajouter des heures supp directement à l'ajout d'un sapeur
            // 'sapeurs.*.heures.*.quantite' => 'required|nullable|numeric',
            // 'sapeurs.*.heures.*.heure_exercice_type_id' => 'required|integer',
        ]);

        $sapeur = $this->service->addSapeurs($exerciceId, $data['sapeurs']);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function update(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*.id' => 'required|integer',
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.amende' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'integer|required',
            'sapeurs.*.excuse_type_id' => 'nullable|integer|exists:excuse_types,id',
            'sapeurs.*.heures.*.id' => 'nullable|integer',
            'sapeurs.*.heures.*.quantite' => 'nullable|numeric',
            'sapeurs.*.heures.*.heure_exercice_type_id' => 'nullable|integer',
        ]);

        // Récupération des permissions
        $sisKey = $request->header('Sis-Id', Null);
        if (is_null($sisKey)) {
            return response()->json(["error" => "Sis non sélectionné"], 401);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé"], 401);
        }

        // Check has role for provided sis
        $perms = (array) $token->data->permissions;
        if (!array_key_exists($sisKey, $perms)) {
            return response()->json(["error" => "Aucun droit pour ce sis"], 401);
        }
        $hasValidationPremission = in_array('exercice.validation', $perms[$sisKey]);

        // Appel du business
        $sapeur = $this->service->updateSapeurs($exerciceId, $data['sapeurs'], $hasValidationPremission);

        return response()->json(['data' => $sapeur]);
    }

    /**
     * Update les présences sans tenir compte d'un exercice en particulier
     *
     * @return Response
     */
    public function updatePresences(Request $request)
    {
        $data = $request->validate([
            'presences.*.id' => 'integer|min:1',
            'presences.*.exercice_id' => 'integer|min:1',
            'presences.*.sapeur_id' => 'integer|min:1',
            'presences.*.convoque' => 'required|integer',
            'presences.*.present' => 'required|integer',
            'presences.*.remplace' => 'required|integer',
            'presences.*.excuse_type_id' => 'nullable|integer',
            'presences.*.amende' => 'required|boolean',
        ]);

        $presences = $this->service->updateSapeurPresences($data['presences']);

        return response()->json(['data' => $presences]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $exerciceId
     * @return Response
     * @throws Exception
     */
    public function presences(Request $request, int $exerciceId)
    {
        $data = $request->validate([
            'sapeurs.*.id' => 'nullable|integer',
            'sapeurs.*.convoque' => 'required|boolean',
            'sapeurs.*.present' => 'required|boolean',
            'sapeurs.*.amende' => 'required|boolean',
            'sapeurs.*.remplace' => 'required|boolean',
            'sapeurs.*.sapeur_id' => 'integer|required',
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
     * Annule la présence de sapeurs
     *
     * @param Request $request
     * @param int $sapeurId
     * @return Response
     */
    public function supprimerConvocations(Request $request, int $sapeurId)
    {

        $data = $request->validate([
            'convocations.*' => 'integer'
        ]);

        $statut = $this->service->supprimerConvocations($sapeurId, $data['convocations']);

        return response()->json(['data' => $statut]);
    }
}
