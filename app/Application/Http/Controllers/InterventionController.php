<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Intervention;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InterventionController extends Controller
{

    public function index(Request $request)
    {
        $exercice_comptable_id = $request->input('exercice_comptable_id');
        $interventions = Intervention::where('exercice_comptable_id', $exercice_comptable_id)->get();
        return response()->json(['data' => $interventions]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date_debut' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_fin' => 'required|date_format:H:i',
            'lieu' => 'string|nullable',
            'objet' => 'string',
            'rapport_police' => 'boolean',
            'agent' => 'string|nullable',
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0',
            'sauve_animaux' => 'integer|min:0',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string|nullable',
            'statut' => 'boolean',
            'localite_id' => 'integer|min:1',
            'exercice_comptable_id' => 'integer|min:1',
            'intervention_traitement_id' => 'integer|min:1',
            'stat_federal_id' => 'integer|min:1',
            'sapeur_id' => 'integer|min:1',
            'type_intervention_id' => 'integer|min:1',
        ]);

        $intervention = InterventionBusiness::createIntervention($data);
        return response()->json(['data' => $intervention]);
    }

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
            'agent' => 'string|nullable',
            'degre' => 'integer|min:1|max:4|required',
            'sauve_personne' => 'integer|min:0',
            'sauve_animaux' => 'integer|min:0',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string|nullable',
            'statut' => 'boolean',
            'localite_id' => 'integer|min:1|required',
            'stat_federal_id' => 'integer|min:1|required',
            'sapeur_id' => 'integer|min:1|required',
            'type_intervention_id' => 'integer|min:1|required',
        ]);

        $quittances = $request->validate(['quittances.*' => 'integer']);
        $quittances = isset($quittances['quittances']) ? $quittances['quittances'] : [];
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
            'missions.*.sapeur_id' => 'integer|exists:sapeurs,id|required_without:missions.*.sapeur',
            'missions.*.sapeur' => 'string|required_without:missions.*.sapeur_id',
        ]);
        $missions = isset($missions['missions']) ? $missions['missions'] : [];
        $appels = $request->validate([
            'appels.*.date' => 'string|required',
            'appels.*.numero' => 'string|required',
            'appels.*.nom' => 'string|required',
            'appels.*.commentaire' => 'string|nullable',
        ]);
        $appels = isset($appels['appels']) ? $appels['appels'] : [];
        $vehicules = $request->validate(['vehicules.*' => 'integer']);
        $vehicules = isset($vehicules['vehicules']) ? $vehicules['vehicules'] : [];
        $groupes = $request->validate([
            'groupes.*.no' => 'nullable|max:10',
            'groupes.*.designation' => 'string|required',
        ]);
        $groupes = isset($groupes['groupes']) ? $groupes['groupes'] : [];
        $materiel = $request->validate([
            'materiel.*.materiel_id' => 'integer|required',
            'materiel.*.quantite' => 'numeric|required',
        ]);
        $materiel = isset($materiel['materiel']) ? $materiel['materiel'] : [];

        try {
            $intervention = InterventionBusiness::importIntervention($intervention, $sapeurs, $groupes, $missions, $appels, $vehicules, $materiel, $quittances);
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

    public function show($id)
    {
        $intervention = Intervention::find($id);
        return response()->json(['data' => $intervention]);
    }

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
            'agent' => 'string|nullable',
            'degre' => 'integer|min:1|max:4',
            'sauve_personne' => 'integer|min:0|max:50',
            'sauve_animaux' => 'integer|min:0|max:50',
            'description' => 'string|nullable',
            'proprietaire' => 'string|nullable',
            'responsable' => 'string|nullable',
            'stat_nb' => 'integer|min:0',
            'wgs84' => 'string|nullable',
            'localite_id' => 'integer|min:1',
            'exercice_comptable_id' => 'integer|min:1',
            'intervention_traitement_id' => 'integer|min:1',
            'stat_federal_id' => 'integer|min:1',
            'sapeur_id' => 'integer|min:1',
            'type_intervention_id' => 'integer|min:1',
        ]);

        $intervention = InterventionBusiness::editInterventionInformationsById($id, $data);
        return response()->json(['data' => $intervention]);
    }

    public function valider($id)
    {
        $statut = InterventionBusiness::validerInterventionById($id);
        return response()->json(['data' => $statut]);
    }

    public function rapport(Request $request, $id)
    {
        $request->merge([
            'infoGeneral' => $request->input('infoGeneral') == 'true',
            'description' => $request->input('description') == 'true',
            'groupes' => $request->input('groupes') == 'true',
            'presences' => $request->input('presences') == 'true',
            'presencesResume' => $request->input('presencesResume') == 'true',
            'montants' => $request->input('montants') == 'true',
            'vehicules' => $request->input('vehicules') == 'true',
            'materiel' => $request->input('materiel') == 'true',
            'absents' => $request->input('absents') == 'true',
            'statut' => $request->input('statut') == 'true',
            'missions' => $request->input('missions') == 'true',
            'appels' => $request->input('appels') == 'true',
        ]);

        $params = $request->validate([
            'infoGeneral' => 'boolean',
            'description' => 'boolean',
            'groupes' => 'boolean',
            'presences' => 'boolean',
            'presencesResume' => 'boolean',
            'montants' => 'boolean',
            'vehicules' => 'boolean',
            'materiel' => 'boolean',
            'absents' => 'boolean',
            'statut' => 'boolean',
            'missions' => 'boolean',
            'appels' => 'boolean',
        ]);

        $sisKey = $request->header('Sis-Key', Null);
        return InterventionBusiness::rapport($id, $params, $sisKey);
    }

    public function destroy($id)
    {
        $statut = InterventionBusiness::deleteInterventionById($id);
        return response()->json(['data' => $statut]);
    }
}
