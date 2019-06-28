<?php


namespace App\Services;

use App\Business\InterventionBusiness;
use App\Contracts\InterventionRepository;
use App\Exceptions\ArrayValidatorException;
use App\Models\Intervention;
use Illuminate\Database\Eloquent\Collection;

class InterventionService
{
    protected $repository;
    protected $business;

    public function __construct(InterventionRepository $repository, InterventionBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function listeIntervention($exercice_comptable_id)
    {
        return $this->repository->listeIntervention($exercice_comptable_id);
    }

    public function getInterventionById($interventionId)
    {
        return $this->repository->findInterventionById($interventionId);
    }

    public function createIntervention($data)
    {
        return $this->business->createIntervention($data);
    }

    public function getInterventionAppels($interventionId)
    {
        return $this->repository->getInterventionAppels($interventionId);
    }

    public function getInterventionMissions($interventionId)
    {
        return $this->repository->getInterventionMissions($interventionId);
    }

    public function getInterventionVehicules($interventionId)
    {
        return $this->repository->getInterventionVehicules($interventionId);
    }

    public function getInterventionMateriels($interventionId)
    {
        return $this->repository->getInterventionMateriels($interventionId);
    }

    public function getInterventionPhases($interventionId)
    {
        return $this->repository->getInterventionPhases($interventionId);
    }

    public function getInterventionQuittances($interventionId)
    {
        return $this->repository->getInterventionQuittances($interventionId);
    }

    public function getInterventionPresences($interventionId)
    {
        return $this->repository->getInterventionPresences($interventionId);
    }

    public function getInterventionGroupes($interventionId)
    {
        return $this->repository->getInterventionGroupes($interventionId);
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
        return $this->business->editInterventionInformationsById($intervention_id, $data);
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public function deleteInterventionById($intervention_id)
    {
        $this->business->deleteInterventionById($intervention_id);
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
        $this->business->addPresences($intervention_id, $sapeurs);
        return $this->repository->getInterventionPresences($intervention_id);
    }

    /**
     * Modification de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updatePresences($intervention_id, $sapeurs)
    {
        $this->business->updatePresences($intervention_id, $sapeurs);
        return $this->repository->getInterventionPresences($intervention_id);
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($interventionId, $ids)
    {
        $this->business->removePresences($interventionId, $ids);
    }

    /**
     * Ajout de phases d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function addPhases($intervention_id, $phases)
    {
        $this->business->addPhases($intervention_id, $phases);
        return $this->repository->getInterventionPhases($intervention_id);
    }

    /**
     * Modification de phases d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayValidatorException(
     */
    public function updatePhases($intervention_id, $phases)
    {
        $this->business->updatePhases($intervention_id, $phases);
        return $this->repository->getInterventionPhases($intervention_id);
    }

    /**
     * Suppression de phases d'un intervention
     *
     * @param $data
     */
    public function removePhases($interventionId, $ids)
    {
        $this->business->removePhases($interventionId, $ids);
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
        $this->business->addAppels($intervention_id, $appels);
        return $this->repository->getInterventionAppels($intervention_id);
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
        $this->business->updateAppels($intervention_id, $appels);
        return $this->repository->getInterventionAppels($intervention_id);
    }

    /**
     * Suppression d'appels d'un intervention
     *
     * @param $data
     */
    public function removeAppels($interventionId, $ids)
    {
        $this->business->removeAppels($interventionId, $ids);
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
        $this->business->addMissions($intervention_id, $missions);
        return $this->repository->getInterventionMissions($intervention_id);
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
        $this->business->updateMissions($intervention_id, $missions);
        return $this->repository->getInterventionMissions($intervention_id);
    }

    /**
     * Suppression de missions à un intervention
     *
     * @param $data
     */
    public function removeMissions($interventionId, $ids)
    {
        $this->business->removeMissions($interventionId, $ids);
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
        $this->business->addMateriels($intervention_id, $materiels);
        return $this->repository->getInterventionMateriels($intervention_id);
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
        $this->business->updateMateriels($intervention_id, $materiels);
        return $this->repository->getInterventionMateriels($intervention_id);
    }

    /**
     * Suppression de materiels à un intervention
     *
     * @param $data
     */
    public function removeMateriels($interventionId, $ids)
    {
        $this->business->removeMateriels($interventionId, $ids);
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
        $this->business->addQuittances($intervention_id, $quittances);
        return $this->repository->getInterventionQuittances($intervention_id);
    }

    /**
     * Suppression de quittances à un intervention
     *
     * @param $data
     */
    public function removeQuittances($interventionId, $ids)
    {
        $this->business->removeQuittances($interventionId, $ids);
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
        $this->business->addVehicules($intervention_id, $vehicules);
        return $this->repository->getInterventionVehicules($intervention_id);
    }

    /**
     * Suppression de vehicules à un intervention
     *
     * @param $data
     */
    public function removeVehicules($interventionId, $ids)
    {
        $this->business->removeVehicules($interventionId, $ids);
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
        $this->business->addGroupes($intervention_id, $groupes);
        return $this->repository->getInterventionGroupes($intervention_id);
    }

    /**
     * Suppression de groupes à un intervention
     *
     * @param $data
     */
    public function removeGroupes($interventionId, $ids)
    {
        $this->business->removeGroupes($interventionId, $ids);
    }
}
