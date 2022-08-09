<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{
    protected $service;

    public function __construct(InterventionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $exercice_comptable_id = $request->get('exercice_comptable_id');

        $interventions = $this->service->listeIntervention($exercice_comptable_id);
        return response()->json(['data' => $interventions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date_debut' => 'date',
            'heure_debut' => 'date_format:H:i',
            'date_fin' => 'date|after_or_equal:date_debut',
            'heure_fin' => 'date_format:H:i',
            'lieu' => 'string|nullable',
            'objet' => 'string',
            'rapport_police' => 'boolean',
            'agent' => 'string',
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0',
            'sauve_animaux' => 'integer|min:0',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string',
            'statut' => 'boolean',
            'localite_id' => 'integer|min:1',
            'exercice_comptable_id' => 'integer|min:1',
            'intervention_traitement_id' => 'integer|min:1',
            'stat_federal_id' => 'integer|min:1',
            'sapeur_id' => 'integer|min:1',
            'type_intervention_id' => 'integer|min:1',
        ]);

        $intervention = $this->service->createIntervention($data);

        return response()->json(['data' => $intervention]);
    }

    /**
     * Store a newly created resource in storage with all data provided
     *
     * @param Request $request
     * @return Response
     */
    public function complet(Request $request)
    {
        $intervention = $request->validate([
            'date_debut' => 'date|required',
            'heure_debut' => 'date_format:H:i|required',
            'date_fin' => 'date|after_or_equal:date_debut|required',
            'heure_fin' => 'date_format:H:i|required',
            'lieu' => 'string|nullable',
            'objet' => 'string|required',
            'rapport_police' => 'boolean',
            'agent' => 'string',
            'degre' => 'integer|min:1|max:4|required',
            'sauve_personne' => 'integer|min:0',
            'sauve_animaux' => 'integer|min:0',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string',
            'statut' => 'boolean',
            'localite_id' => 'integer|min:1|required',
            'stat_federal_id' => 'integer|min:1|required',
            'sapeur_id' => 'integer|min:1|required',
            'type_intervention_id' => 'integer|min:1|required',
        ]);

        $sapeurs = $request->validate([
            'sapeurs.*.sapeur_id' => 'integer|required',
            'sapeurs.*.debut' => 'date_format:Y-m-d H:i|required',
            'sapeurs.*.fin' => 'date_format:Y-m-d H:i|required',
            'sapeurs.*.piquet' => 'boolean|required',
        ]);
        $sapeurs = isset($sapeurs['sapeurs']) ? $sapeurs['sapeurs'] : [];

        $missions = $request->validate([
            'missions.*.titre' => 'string|required',
            'missions.*.resume' => 'string|nullable',
            'missions.*.debut' => 'date_format:Y-m-d H:i|required',
            'missions.*.fin' => 'date_format:Y-m-d H:i|required',
            'missions.*.sapeur_id' => 'integer|exists:sapeurs,id',
        ]);
        $missions = isset($missions['missions']) ? $missions['missions'] : [];

        $appels = $request->validate([
            'appels.*.date' => 'string|required',
            'appels.*.numero' => 'string|required',
            'appels.*.nom' => 'string|required',
            'appels.*.commentaire' => 'string|nullable',
        ]);
        $appels = isset($appels['appels']) ? $appels['appels'] : [];

        $vehicules = $request->validate([
            'vehicules.*' => 'integer',
        ]);
        $vehicules = isset($vehicules['vehicules']) ? $vehicules['vehicules'] : [];

        $groupes = $request->validate([
            'groupes.*.no' => 'integer|nullable',
            'groupes.*.designation' => 'string|required',
        ]);
        $groupes = isset($groupes['groupes']) ? $groupes['groupes'] : [];

        $materiel = $request->validate([
            'materiel.*.materiel_id' => 'integer|required',
            'materiel.*.quantite' => 'numeric|required',
        ]);
        $materiel = isset($materiel['materiel']) ? $materiel['materiel'] : [];

        try {
            $intervention = $this->service->importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel);
        } catch (Exception $e) {
            Log::error("Intervention Export", [
                "intervention" => $intervention,
                "sapeurs" => $sapeurs,
                "missions" => $missions,
                "appels" => $appels,
                "vehicules" => $vehicules,
                "groupes" => $groupes,
                "materiel" => $materiel,
                "exception" => $e,
            ]);
            return response()->json(['error' => 'Une erreur est survenue lors de l\'export de votre intervention, contacter l\'administrateur'], 500);
        }

        return response()->json(['data' => $intervention]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $intervention = $this->service->getInterventionById($id);

        return response()->json(['data' => $intervention]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws ArrayException
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'date_debut' => 'date',
            'heure_debut' => 'date_format:H:i',
            'date_fin' => 'date|after_or_equal:date_debut',
            'heure_fin' => 'date_format:H:i',
            'lieu' => 'string|nullable',
            'objet' => 'string',
            'rapport_police' => 'boolean',
            'agent' => 'string',
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0|max:50',
            'sauve_animaux' => 'integer|min:0|max:50',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string',
            'localite_id' => 'integer|min:1',
            'exercice_comptable_id' => 'integer|min:1',
            'intervention_traitement_id' => 'integer|min:1',
            'stat_federal_id' => 'integer|min:1',
            'sapeur_id' => 'integer|min:1',
            'type_intervention_id' => 'integer|min:1',
        ]);

        $intervention = $this->service->editInterventionInformationsById($id, $data);

        return response()->json(['data' => $intervention]);
    }

    public function valider($id)
    {
        $statut = $this->service->validerInterventionById($id);

        return response()->json(['data' => $statut]);
    }

    public function rapport(Request $request, $id)
    {
        $params = $request->validate([
            'infoGeneral' => 'boolean',
            'description' => 'boolean',
            'groupes' => 'boolean',
            'presences' => 'boolean',
            'montants' => 'boolean',
            'vehicules' => 'boolean',
            'materiel' => 'boolean',
            'absents' => 'boolean',
            'statut' => 'boolean',
            'missions' => 'boolean',
            'appels' => 'boolean',
        ]);
        return $this->service->rapport($id, $params);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $statut = $this->service->deleteInterventionById($id);

        return response()->json(['data' => $statut]);
    }
}
