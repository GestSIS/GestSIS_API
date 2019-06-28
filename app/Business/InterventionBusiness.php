<?php


namespace App\Business;


use App\Contracts\InterventionRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use Illuminate\Database\Eloquent\Collection;
use Validator;

class InterventionBusiness
{

    protected $repository;

    public function __construct(InterventionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a intervention
     *
     * @param $data
     * @return InterventionBusiness
     * @throws ArrayValidatorException
     */
    public function createIntervention($data)
    {
        //TODO Vérifier intervention comptable
        $phaseTypeIntervention = 1;

        $intervention = $this->repository->createNewIntervention($data);
        $this->repository->addPhase($intervention->id, array(
            "debut" => $data['date_debut'] . ' ' . $data['heure_debut'],
            "phase_type_id" => $phaseTypeIntervention
        ));
        return $intervention;
    }

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws ArrayValidatorException(
     */
    public function editInterventionInformationsById($intervention_id, $data)
    {
        $intervention = $this->repository->editInterventionInformationsById($intervention_id, $data);
        //TODO Update phase debut
        //TODO Check if date debut changed -> update first phase

        return $intervention;
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public static function deleteInterventionById($intervention_id)
    {
        /* TODO Check:
        - Pas imputé
        */

        /* TODO: Suppression:
        - Sapeur
        - Groupes
        - Vehicules
        - Matériel
        - Quittances
        - Missions
        - Appels
        */
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addPresences($intervention_id, $sapeurs)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($sapeurs as $sapeur) {
            //TODO Check duplicated period of time

            $this->repository->addPresence($intervention_id, $sapeur);
        }
    }

    /**
     * Modification de sapeurs d'une intervention
     *
     * @param $data
     * @return Collection
     */
    public function updatePresences($intervention_id, $sapeurs)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($sapeurs as $sapeur) {
            //TODO Check period non dupliqué

            $this->repository->editPresenceInfoById($intervention_id, $sapeur['sapeur_id'], $sapeur);
        }
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removePresencesById($intervention_id, $ids);
    }

    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addAppels($intervention_id, $appels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($appels as $appel) {
            $this->repository->addAppel($intervention_id, $appel);
        }
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateAppels($intervention_id, $appels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($appels as $appel) {
            $this->repository->editAppelInfoById($intervention_id, $appel['id'], $appel);
        }
    }

    /**
     * Suppression d'appels d'un intervention
     *
     * @param $data
     */
    public function removeAppels($exercice_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */
        $this->repository->removeAppelsById($exercice_id, $ids);
    }

    /**
     * Ajout de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMissions($intervention_id, $missions)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($missions as $mission) {
            $this->repository->addMission($intervention_id, $mission);
        }
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMissions($intervention_id, $missions)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($missions as $mission) {
            $this->repository->editMissionInfoById($intervention_id, $mission['id'], $mission);
        }
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeMissionsById($intervention_id, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addPhases($intervention_id, $phases)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($phases as $phase) {
            $this->repository->addPhase($intervention_id, $phase);
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updatePhases($intervention_id, $phases)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($phases as $phase) {
            $this->repository->editPhaseInfosById($intervention_id, $phase['id'], $phase);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removePhases($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removePhasesById($intervention_id, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMateriels($intervention_id, $materiels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($materiels as $materiel) {
            $this->repository->addMateriel($intervention_id, $materiel);
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMateriels($intervention_id, $materiels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($materiels as $materiel) {
            $this->repository->editMaterielQuantiteById($intervention_id, $materiel['id'], $materiel['quantite']);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeMaterielsById($intervention_id, $ids);
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addQuittances($intervention_id, $quittances)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($quittances as $quittance) {
            $this->repository->addQuittance($intervention_id, $quittance);
        }
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeQuittancesById($intervention_id, $ids);
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addVehicules($intervention_id, $vehicules)
    {
        /* TODO Check:
        - Pas imputé
        - FIXME Check pas de véhicules dupliqués
        */

        foreach ($vehicules as $vehicule) {
            $this->repository->addVehicule($intervention_id, $vehicule);
        }
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeVehiculesById($intervention_id, $ids);
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addGroupes($intervention_id, $groupes)
    {
        /* TODO Check:
        - Pas imputé
        */
        foreach ($groupes as $groupe) {
            $this->repository->addGroupe($intervention_id, $groupe);
        }
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($intervention_id, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeGroupesById($intervention_id, $ids);
    }
}
