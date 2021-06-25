<?php


namespace App\Domaine\SPI;


interface InterventionRepository
{
    public function findWith($intervention_id, $with = []);

    public function listeIntervention($exercice_comptable_id);
    public function findInterventionById($interventionId);

    public function getInterventionStatutById($interventionId);

    public function getInterventionAppels($interventionId);
    public function getInterventionMissions($interventionId);
    public function getInterventionVehicules($interventionId);
    public function getInterventionMateriels($interventionId);
    public function getInterventionPhases($interventionId);
    public function getInterventionQuittances($interventionId);
    public function getInterventionPresences($interventionId);
    public function getInterventionGroupes($interventionId);

    public function createNewIntervention($intervention);
    public function editInterventionInformationsById($interventionId, $infos);
    public function supprimerInterventionById($interventionId);

    public function addPresence($interventionId, $sapeur);
    public function editPresenceInfoById($interventionId, $sapeurId, $infos);
    public function removePresencesById($interventionId, array $ids);

    public function addAppel($interventionId, $appel);
    public function editAppelInfoById($interventionId, $appelId, $infos);
    public function removeAppelsById($interventionId, array $ids);

    public function addMission($interventionId, $mission);
    public function editMissionInfoById($interventionId, $missionId, $infos);
    public function removeMissionsById($interventionId, array $ids);

    public function addMateriel($interventionId, $materiel);
    public function editMaterielQuantiteById($interventionId, $materielId, $quantite);
    public function removeMaterielsById($interventionId, array $ids);

    public function addPhase($interventionId, $phase);
    public function editPhaseInfosById($interventionId, $phaseId, $debut);
    public function removePhasesById($interventionId, array $ids);

    public function addQuittance($interventionId, $sapeurId);
    public function removeQuittancesById($interventionId, array $ids);

    public function addVehicule($interventionId, $vehiculeId);
    public function removeVehiculesById($interventionId, array $ids);

    public function addGroupe($interventionId, $groupeId);
    public function removeGroupesById($interventionId, array $ids);
}
