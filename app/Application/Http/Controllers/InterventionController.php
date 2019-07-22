<?php

namespace App\Application\Http\Controllers;

use App\Domaine\API\InterventionService;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0|max:50',
            'sauve_animaux' => 'integer|min:0|max:50',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'statut' => 'boolean',
            'localite_id' => 'integer|min:1',
            'intervention_comptable_id' => 'integer|min:1',
            'intervention_traitement_id' => 'integer|min:1',
            'stat_federal_id' => 'integer|min:1',
            'sapeur_id' => 'integer|min:1',
            'type_intervention_id' => 'integer|min:1',
        ]);

        $intervention = $this->service->createIntervention($data);

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
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0|max:50',
            'sauve_animaux' => 'integer|min:0|max:50',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'localite_id' => 'integer|exists:localites,id',
            'intervention_comptable_id' => 'integer|exists:intervention_comptables,id',
            'intervention_traitement_id' => 'integer|exists:intervention_traitements,id',
            'stat_federal_id' => 'integer|exists:stat_federals,id',
            'sapeur_id' => 'integer|exists:sapeurs,id',
            'type_intervention_id' => 'integer|exists:type_interventions,id',
        ]);

        $intervention = $this->service->editInterventionInformationsById($id, $data);

        return response()->json(['data' => $intervention]);
    }

    public function valider($id)
    {
        $statut = $this->service->validerInterventionById($id);

        return response()->json(['data' => $statut]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //TODO
    }
}
