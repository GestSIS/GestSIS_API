<?php


namespace App\Business;


use App\Contracts\InterventionRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Validator;


class InterventionBusiness
{

    public const INTERVENTION_STATUT_EMPTY = 0;
    public const INTERVENTION_STATUT_SAISI = 1;
    public const INTERVENTION_STATUT_VALIDE = 2;
    public const INTERVENTION_STATUT_IMPUTE = 3;

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
        $data['statut'] = self::INTERVENTION_STATUT_EMPTY;

        $intervention = $this->repository->createNewIntervention($data);
        $this->repository->addPhase($intervention->id, array(
            "debut" => null,
            "phase_type_id" => $phaseTypeIntervention,
            "statut" => self::INTERVENTION_STATUT_EMPTY
        ));
        return $intervention;
    }

    /**
     * @param $interventionId
     * @return mixed
     * @throws ArrayValidatorException
     */
    public function validerInterventionById($interventionId)
    {
        $statut = $this->repository->getInterventionStatutById($interventionId);
        if ($statut === self::INTERVENTION_STATUT_SAISI) {
            return $this->repository->editInterventionInformationsById($interventionId, [
                "statut" => self::INTERVENTION_STATUT_VALIDE
            ])->statut;
        }
        throw new ArrayValidatorException(["message" => "Impossible de valider l'exercice."]);
    }

    /**
     * Updates a intervention.
     *
     * @param int
     * @param array
     * @return Intervention
     * @throws ArrayValidatorException(
     */
    public function editInterventionInformationsById($interventionId, $data)
    {
        $intervention = $this->repository->editInterventionInformationsById($interventionId, $data);
        //TODO Update phase debut
        //TODO Check if date debut changed -> update first phase

        return $intervention;
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public static function deleteInterventionById($interventionId)
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
    public function addPresences($interventionId, $sapeurs)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($sapeurs as $sapeur) {
            //TODO Check duplicated period of time

            $this->repository->addPresence($interventionId, $sapeur);
        }

        $statut = $this->repository->getInterventionStatutById($interventionId);
        if ($statut < self::INTERVENTION_STATUT_SAISI) {
            $statut = $this->repository->editInterventionInformationsById($interventionId, ["statut" => self::INTERVENTION_STATUT_SAISI])->statut;
        }
        return $statut;
    }

    /**
     * Modification de sapeurs d'une intervention
     *
     * @param $data
     * @return Collection
     */
    public function updatePresences($interventionId, $sapeurs)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($sapeurs as $sapeur) {
            //TODO Check period non dupliqué

            $this->repository->editPresenceInfoById($interventionId, $sapeur['id'], $sapeur);
        }
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removePresencesById($interventionId, $ids);
        $statut = $this->repository->getInterventionStatutById($interventionId);

        if (count($this->repository->getInterventionPresences($interventionId)) === 0) {
            $statut = $this->repository->editInterventionInformationsById($interventionId, ["statut" => self::INTERVENTION_STATUT_EMPTY]);
        }
        return $statut;
    }

    /**
     * Ajout d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addAppels($interventionId, $appels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($appels as $appel) {
            $this->repository->addAppel($interventionId, $appel);
        }
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateAppels($interventionId, $appels)
    {
        /* TODO Check:
        - Pas imputé
        */
        foreach ($appels as $appel) {
            $this->repository->editAppelInfoById($interventionId, $appel['id'], $appel);
        }
    }

    /**
     * Suppression d'appels d'une intervention
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
    public function addMissions($interventionId, $missions)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($missions as $mission) {
            $this->repository->addMission($interventionId, $mission);
        }
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMissions($interventionId, $missions)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($missions as $mission) {
            $this->repository->editMissionInfoById($interventionId, $mission['id'], $mission);
        }
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeMissionsById($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addPhases($interventionId, $phases)
    {
        /* TODO Check:
        - Pas imputé
        */
        $intervention = $this->repository->findInterventionById($interventionId);
        $existingPhases = $this->repository->getInterventionPhases($interventionId);

        $debut = Carbon::parse($intervention->date_debut . " " . $intervention->heure_debut);
        foreach ($phases as $phase) {
            if ($debut >= Carbon::parse($phase['debut'])) {
                throw new ArrayValidatorException(["debut" => "Debut trop tôt"]);
            } else {
                foreach ($existingPhases as $existingPhase) {
                    if ($existingPhase->debut !== null && $debut == Carbon::parse($existingPhase->debut)) {
                        throw new ArrayValidatorException(["debut" => "Debut trop tôt"]);
                    }
                }
                $this->repository->addPhase($interventionId, $phase);
            }
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updatePhases($interventionId, $phases)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($phases as $phase) {
            $this->repository->editPhaseInfosById($interventionId, $phase['id'], $phase);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removePhases($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removePhasesById($interventionId, $ids);
    }

    /**
     * Ajout de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addMateriels($interventionId, $materiels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($materiels as $materiel) {
            $this->repository->addMateriel($interventionId, $materiel);
        }
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updateMateriels($interventionId, $materiels)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($materiels as $materiel) {
            $this->repository->editMaterielQuantiteById($interventionId, $materiel['id'], $materiel['quantite']);
        }
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeMaterielsById($interventionId, $ids);
    }

    /**
     * Ajout de quittances à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addQuittances($interventionId, $quittances)
    {
        /* TODO Check:
        - Pas imputé
        */

        foreach ($quittances as $quittance) {
            $this->repository->addQuittance($interventionId, $quittance);
        }
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeQuittancesById($interventionId, $ids);
    }

    /**
     * Ajout de vehicules à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addVehicules($interventionId, $vehicules)
    {
        /* TODO Check:
        - Pas imputé
        - FIXME Check pas de véhicules dupliqués
        */

        foreach ($vehicules as $vehicule) {
            $this->repository->addVehicule($interventionId, $vehicule);
        }
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeVehiculesById($interventionId, $ids);
    }

    /**
     * Ajout de groupes à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addGroupes($interventionId, $groupes)
    {
        /* TODO Check:
        - Pas imputé
        */
        foreach ($groupes as $groupe) {
            $this->repository->addGroupe($interventionId, $groupe);
        }
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($interventionId, $ids)
    {
        /* TODO Check:
        - Pas imputé
        */

        $this->repository->removeGroupesById($interventionId, $ids);
    }
}
