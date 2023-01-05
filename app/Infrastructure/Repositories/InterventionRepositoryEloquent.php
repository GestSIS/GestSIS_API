<?php


namespace App\Infrastructure\Repositories;

use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\Appel;
use App\Infrastructure\Models\GroupeIntervention;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\InterventionMateriel;
use App\Infrastructure\Models\InterventionSapeur;
use App\Infrastructure\Models\InterventionVehicule;
use App\Infrastructure\Models\Mission;
use App\Infrastructure\Models\Phase;
use App\Infrastructure\Models\Quittance;
use StdClass;

class InterventionRepositoryEloquent implements InterventionRepository
{

    public function listeIntervention($exercice_comptable_id)
    {
        $temp = $this;
        return Intervention::where('exercice_comptable_id', $exercice_comptable_id)
            ->get()
            ->map(function ($intervention) use ($temp) {
                return $temp->convertIntervention($intervention);
            })->toArray();
    }

    /**
     * @param $id
     * @param $with
     * @return StdClass|null
     */
    public function findWith($id, $with = array()) //TODO IMPROVE THIS FUNCTION
    {
        //TODO Check with allowed
        $allowedWith = ['presences', 'phases', 'localite'];
        return $this->convertIntervention(Intervention::with($with)->find($id), $with);
    }

    public function getInterventionStatutById($interventionId)
    {
        return Intervention::findOrFail($interventionId, 'statut')->statut;
    }

    public function findInterventionById($interventionId)
    {
        return $this->convertIntervention(Intervention::find($interventionId));
    }

    public function createNewIntervention($data)
    {
        if (!array_key_exists('lieu', $data) || $data['lieu'] === null) $data['lieu'] = '';
        if (!array_key_exists('agent', $data) || $data['agent'] === null) $data['agent'] = '';
        if (!array_key_exists('description', $data) || $data['description'] === null) $data['description'] = '';
        if (!array_key_exists('proprietaire', $data) || $data['proprietaire'] === null) $data['proprietaire'] = '';
        if (!array_key_exists('responsable', $data) || $data['responsable'] === null) $data['responsable'] = '';
        if (array_key_exists('wgs84', $data) && $data['wgs84'] === null) $data['wgs84'] = '';

        $intervention = new Intervention();
        $intervention->fill($data);
        $intervention->date_imputation = null;
        $intervention->exercice_comptable_id = $data['exercice_comptable_id'];
        $intervention->save();

        return $this->convertIntervention($intervention);
    }

    public function editInterventionInformationsById($interventionId, $data)
    {
        if (array_key_exists('lieu', $data) && $data['lieu'] === null) $data['lieu'] = '';
        if (!array_key_exists('agent', $data) || $data['agent'] === null) $data['agent'] = '';
        if (array_key_exists('description', $data) && $data['description'] === null) $data['description'] = '';
        if (array_key_exists('proprietaire', $data) && $data['proprietaire'] === null) $data['proprietaire'] = '';
        if (array_key_exists('responsable', $data) && $data['responsable'] === null) $data['responsable'] = '';
        if (array_key_exists('wgs84', $data) && $data['wgs84'] === null) $data['wgs84'] = '';

        $intervention = Intervention::find($interventionId);
        $intervention->update($data);

        return $this->convertIntervention($intervention);
    }

    public function supprimerInterventionById($interventionId)
    {
        InterventionSapeur::where('intervention_id', '=', $interventionId)->delete();
        GroupeIntervention::where('intervention_id', '=', $interventionId)->delete();
        InterventionVehicule::where('intervention_id', '=', $interventionId)->delete();
        InterventionMateriel::where('intervention_id', '=', $interventionId)->delete();
        Quittance::where('intervention_id', '=', $interventionId)->delete();
        Mission::where('intervention_id', '=', $interventionId)->delete();
        Appel::where('intervention_id', '=', $interventionId)->delete();
        Phase::where('intervention_id', '=', $interventionId)->delete();
        Intervention::where('id', '=', $interventionId)->delete();
    }

    public function getInterventionAppels($interventionId)
    {
        $temp = $this;
        return Appel::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertAppel($intervention);
            })->toArray();
    }

    public function getInterventionMissions($interventionId)
    {
        $temp = $this;
        return Mission::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertMission($intervention);
            })->toArray();
    }

    public function getInterventionVehicules($interventionId)
    {
        $temp = $this;
        return InterventionVehicule::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertVehicule($intervention);
            })->toArray();
    }

    public function getInterventionMateriels($interventionId)
    {
        $temp = $this;
        return InterventionMateriel::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertMateriel($intervention);
            })->toArray();
    }

    public function getInterventionPhases($interventionId)
    {
        $temp = $this;
        return Phase::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertPhase($intervention);
            })->toArray();
    }

    public function getInterventionQuittances($interventionId)
    {
        $temp = $this;
        return Quittance::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertQuittance($intervention);
            })->toArray();
    }

    public function getInterventionPresences($interventionId)
    {
        $temp = $this;
        return InterventionSapeur::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertPresence($intervention);
            })->toArray();
    }

    public function getInterventionGroupes($interventionId)
    {
        $temp = $this;
        return GroupeIntervention::where('intervention_id', $interventionId)
            ->get()->map(function ($intervention) use ($temp) {
                return $temp->convertGroupe($intervention);
            })->toArray();
    }

    public function addPresence($interventionId, $presence)
    {
        $sap = new InterventionSapeur();
        $sap->fill($presence);
        $sap->sapeur_id = $presence['sapeur_id'];
        $sap->intervention_id = $interventionId;
        $sap->save();
    }

    public function editPresenceInfoById($interventionId, $presenceId, $infos)
    {
        InterventionSapeur::where('intervention_id', $interventionId)->where('id', $presenceId)->update($infos);
        return $this->convertPresence(InterventionSapeur::find($presenceId));
    }

    public function removePresencesById($interventionId, array $ids)
    {
        InterventionSapeur::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addAppel($interventionId, $appel)
    {
        if (array_key_exists('commentaire', $appel) && $appel['commentaire'] === null) $appel['commentaire'] = '';

        $app = new Appel();
        $app->fill($appel);
        $app->intervention_id = $interventionId;
        $app->save();
    }

    public function editAppelInfoById($interventionId, $appelId, $infos)
    {
        if (array_key_exists('commentaire', $infos) && $infos['commentaire'] === null) $infos['commentaire'] = '';

        Appel::where('intervention_id', $interventionId)->where('id', $appelId)->update($infos);
        return $this->convertAppel(Appel::find($appelId));
    }

    public function removeAppelsById($interventionId, array $ids)
    {
        Appel::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addMission($interventionId, $mission)
    {
        if (array_key_exists('resume', $mission) && $mission['resume'] === null) $mission['resume'] = '';

        $mis = new Mission();
        $mis->fill($mission);
        $mis->intervention_id = $interventionId;
        $mis->save();
    }

    public function editMissionInfoById($interventionId, $missionId, $infos)
    {
        if (array_key_exists('resume', $infos) && $infos['resume'] === null) $infos['resume'] = '';

        Mission::where('intervention_id', $interventionId)->where('id', $missionId)->update($infos);
        return $this->convertMission(Mission::find($missionId));
    }

    public function removeMissionsById($interventionId, array $ids)
    {
        Mission::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addMateriel($interventionId, $materiel)
    {
        $mis = new InterventionMateriel();
        $mis->fill($materiel);
        $mis->materiel_id = $materiel['materiel_id'];
        $mis->intervention_id = $interventionId;
        $mis->save();
    }

    public function editMaterielQuantiteById($interventionId, $materielId, $quantite)
    {
        InterventionMateriel
            ::where('intervention_id', $interventionId)
            ->where('id', $materielId)
            ->update(['quantite' => $quantite]);
        return $this->convertMateriel(InterventionMateriel::find($materielId));
    }

    public function removeMaterielsById($interventionId, array $ids)
    {
        InterventionMateriel::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addPhase($interventionId, $data)
    {
        $phase = new Phase();
        $phase->fill($data);
        $phase->intervention_id = $interventionId;
        $phase->save();
        return $phase;
    }

    public function editPhaseInfosById($interventionId, $phaseId, $phase)
    {
        Phase::where('intervention_id', $interventionId)->where('id', $phaseId)->update($phase);
        return $this->convertPhase(Phase::find($phaseId));
    }

    public function removePhasesById($interventionId, array $ids)
    {
        Phase::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addQuittance($interventionId, $sapeurId)
    {
        $quittance = new Quittance();
        $quittance->sapeur_id = $sapeurId;
        $quittance->intervention_id = $interventionId;
        $quittance->save();
        return $quittance;
    }

    public function removeQuittancesById($interventionId, array $ids)
    {
        Quittance::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addVehicule($interventionId, $vehiculeId)
    {
        $vehicule = new InterventionVehicule();
        $vehicule->vehicule_id = $vehiculeId;
        $vehicule->intervention_id = $interventionId;
        $vehicule->save();
        return $vehicule;
    }

    public function removeVehiculesById($interventionId, array $ids)
    {
        InterventionVehicule::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    public function addGroupe($interventionId, $no, $designation)
    {
        $groupe = new GroupeIntervention();
        $groupe->no = $no;
        $groupe->designation = $designation;
        $groupe->intervention_id = $interventionId;
        $groupe->save();
        return $groupe;
    }

    public function removeGroupesById($interventionId, array $ids)
    {
        GroupeIntervention::where('intervention_id', $interventionId)->whereIn('id', $ids)->delete();
    }

    /**
     * @param $intervention
     * @return StdClass|null
     */
    protected function convertIntervention($intervention, $with = array())
    {
        if ($intervention == null) return null;

        $object = new StdClass();
        $object->id = $intervention->id;

        $object->date_debut = $intervention->date_debut;
        $object->heure_debut = $intervention->heure_debut;
        $object->lieu = $intervention->lieu;
        $object->objet = $intervention->objet;
        $object->date_fin = $intervention->date_fin;
        $object->heure_fin = $intervention->heure_fin;
        $object->rapport_police = $intervention->rapport_police;
        $object->agent = $intervention->agent;
        $object->degre = $intervention->degre;
        $object->sauve_personne = $intervention->sauve_personne;
        $object->sauve_animaux = $intervention->sauve_animaux;
        $object->description = $intervention->description;
        $object->proprietaire = $intervention->proprietaire;
        $object->responsable = $intervention->responsable;
        $object->stat_nb = $intervention->stat_nb;
        $object->wgs84 = $intervention->wgs84;
        $object->statut = $intervention->statut;
        $object->date_imputation = $intervention->date_imputation;
        $object->exercice_comptable_id = $intervention->exercice_comptable_id;
        $object->localite_id = $intervention->localite_id;
        $object->type_intervention_id = $intervention->type_intervention_id;
        $object->presence_id = $intervention->presence_id;
        $object->stat_federal_id = $intervention->stat_federal_id;
        $object->sapeur_id = $intervention->sapeur_id;
        $object->intervention_traitement_id = $intervention->intervention_traitement_id;

        if (in_array('presences', $with)) {
            $temp = $this;
            $object->presences = $intervention->presences->map(function ($sap) use ($temp) {
                return $temp->convertPresence($sap);
            })->toArray();
        }

        if (in_array('phases', $with)) {
            $temp = $this;
            $object->phases = $intervention->phases->map(function ($sap) use ($temp) {
                return $temp->convertPhase($sap);
            })->toArray();
        }

        if (in_array('localite', $with)) {
            $object->localite = $this->convertLocalite($intervention->localite);
        }

        if (in_array('typeIntervention', $with)) {
            $object->type = $this->convertTypeIntervention($intervention->typeIntervention);
        }

        return $object;
    }

    protected function convertTypeIntervention($type)
    {
        if ($type == null) return null;

        $object = new StdClass();
        $object->id = $type->id;

        $object->designation = $type->designation;

        return $object;
    }

    protected function convertLocalite($localite)
    {
        if ($localite == null) return null;

        $object = new StdClass();
        $object->id = $localite->id;

        $object->npa = $localite->npa;
        $object->designation = $localite->designation;

        return $object;
    }

    protected function convertPhase($phase)
    {
        if ($phase == null) return null;

        $object = new StdClass();
        $object->id = $phase->id;

        $object->debut = $phase->debut;
        $object->phase_type_id = $phase->phase_type_id;
        $object->intervention_id = $phase->intervention_id;

        return $object;
    }

    protected function convertPresence($presence)
    {
        if ($presence == null) return null;

        $object = new StdClass();
        $object->id = $presence->id;

        $object->debut = $presence->debut;
        $object->fin = $presence->fin;
        $object->piquet = $presence->piquet;
        $object->sapeur_id = $presence->sapeur_id;
        $object->intervention_id = $presence->intervention_id;

        return $object;
    }

    protected function convertMission($mission)
    {
        if ($mission == null) return null;

        $object = new StdClass();
        $object->id = $mission->id;

        $object->debut = $mission->debut;
        $object->fin = $mission->fin;
        $object->titre = $mission->titre;
        $object->resume = $mission->resume;
        $object->sapeur_id = $mission->sapeur_id;
        $object->sapeur = $mission->sapeur;
        $object->intervention_id = $mission->intervention_id;

        return $object;
    }

    protected function convertAppel($appel)
    {
        if ($appel == null) return null;

        $object = new StdClass();
        $object->id = $appel->id;

        $object->numero = $appel->numero;
        $object->date = $appel->date;
        $object->nom = $appel->nom;
        $object->commentaire = $appel->commentaire;
        $object->intervention_id = $appel->intervention_id;

        return $object;
    }

    protected function convertVehicule($vehicule)
    {
        if ($vehicule == null) return null;

        $object = new StdClass();
        $object->id = $vehicule->id;

        $object->vehicule_id = $vehicule->vehicule_id;
        $object->intervention_id = $vehicule->intervention_id;

        return $object;
    }

    protected function convertMateriel($materiel)
    {
        if ($materiel == null) return null;

        $object = new StdClass();
        $object->id = $materiel->id;

        $object->quantite = $materiel->quantite;
        $object->materiel_id = $materiel->materiel_id;
        $object->intervention_id = $materiel->intervention_id;

        return $object;
    }

    protected function convertQuittance($quittance)
    {
        if ($quittance == null) return null;

        $object = new StdClass();
        $object->id = $quittance->id;

        $object->sapeur_id = $quittance->sapeur_id;
        $object->intervention_id = $quittance->intervention_id;

        return $object;
    }

    protected function convertGroupe($groupe)
    {
        if ($groupe == null) return null;

        $object = new StdClass();
        $object->id = $groupe->id;

        $object->no = $groupe->no;
        $object->designation = $groupe->designation;
        $object->intervention_id = $groupe->intervention_id;

        return $object;
    }
}
