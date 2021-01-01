<?php


namespace App\Domaine\API;

use App\Domaine\Business\InterventionBusiness;
use App\Domaine\SPI\InterventionRepository;
use App\Infrastructure\Models\Intervention;
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
     * @throws ArrayException(
     */
    public function editInterventionInformationsById($interventionId, $data)
    {
        return $this->business->editInterventionInformationsById($interventionId, $data);
    }

    public function validerInterventionById($interventionId){
        return $this->business->validerInterventionById($interventionId);
    }

    /**
     * Delete a intervention.
     *
     * @param int
     */
    public function deleteInterventionById($interventionId)
    {
        $this->business->deleteInterventionById($interventionId);
    }

    /**
     * Ajout de sapeurs d'un intervention
     *
     * @param $data
     * @return array
     * @throws ArrayException(
     */
    public function addPresences($interventionId, $sapeurs)
    {
        $statut = $this->business->addPresences($interventionId, $sapeurs);
        return [
            "statut" => $statut,
            "sapeurs" => $this->repository->getInterventionPresences($interventionId)
        ];
    }

    /**
     * Modification de sapeurs d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updatePresences($interventionId, $sapeurs)
    {
        $this->business->updatePresences($interventionId, $sapeurs);
        return $this->repository->getInterventionPresences($interventionId);
    }

    /**
     * Suppression de sapeurs d'un intervention
     *
     * @param $data
     */
    public function removePresences($interventionId, $ids)
    {
        return $this->business->removePresences($interventionId, $ids);
    }

    /**
     * Ajout de phases d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function addPhases($interventionId, $phases)
    {
        $this->business->addPhases($interventionId, $phases);
        return $this->repository->getInterventionPhases($interventionId);
    }

    /**
     * Modification de phases d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updatePhases($interventionId, $phases)
    {
        $this->business->updatePhases($interventionId, $phases);
        return $this->repository->getInterventionPhases($interventionId);
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
     * @throws ArrayException(
     */
    public function addAppels($interventionId, $appels)
    {
        $this->business->addAppels($interventionId, $appels);
        return $this->repository->getInterventionAppels($interventionId);
    }

    /**
     * Modification d'appels d'un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateAppels($interventionId, $appels)
    {
        $this->business->updateAppels($interventionId, $appels);
        return $this->repository->getInterventionAppels($interventionId);
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
     * @throws ArrayException(
     */
    public function addMissions($interventionId, $missions)
    {
        $this->business->addMissions($interventionId, $missions);
        return $this->repository->getInterventionMissions($interventionId);
    }

    /**
     * Modification de missions à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateMissions($interventionId, $missions)
    {
        $this->business->updateMissions($interventionId, $missions);
        return $this->repository->getInterventionMissions($interventionId);
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
     * @throws ArrayException(
     */
    public function addMateriels($interventionId, $materiels)
    {
        $this->business->addMateriels($interventionId, $materiels);
        return $this->repository->getInterventionMateriels($interventionId);
    }

    /**
     * Modification de materiels à un intervention
     *
     * @param $data
     * @return Collection
     * @throws ArrayException(
     */
    public function updateMateriels($interventionId, $materiels)
    {
        $this->business->updateMateriels($interventionId, $materiels);
        return $this->repository->getInterventionMateriels($interventionId);
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
     * @throws ArrayException(
     */
    public function addQuittances($interventionId, $quittances)
    {
        $this->business->addQuittances($interventionId, $quittances);
        return $this->repository->getInterventionQuittances($interventionId);
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
     * @throws ArrayException(
     */
    public function addVehicules($interventionId, $vehicules)
    {
        $this->business->addVehicules($interventionId, $vehicules);
        return $this->repository->getInterventionVehicules($interventionId);
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
     * @throws ArrayException(
     */
    public function addGroupes($interventionId, $groupes)
    {
        $this->business->addGroupes($interventionId, $groupes);
        return $this->repository->getInterventionGroupes($interventionId);
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

    public function rapport($interventionId, $params)
    {
        $intervention = Intervention::find($interventionId);
        // $missions = Mission::where('interventionId', '=', $interventionId)->all();
        // $appels = Appel::where('interventionId', '=', $interventionId)->all();
        // $appels = Appel::where('interventionId', '=', $interventionId)->all();
        // Intervention::join('missions')->join('appels')->join()
        // Intervention::with(['sapInt', 'sapeurs'])
        // $intervention = $this->repository->getIntervention($interventionId, ['sapeurs', 'localite']);
        // $sapeurs = $this->sapeurRepository->listeSapeurLight();
        // $exercice->sapeurs = array_map(function($s) use($sapeurs) {
        //     $id = $s->sapeur_id;
        //     $sap = array_values(array_filter($sapeurs, function($sapeur) use ($id) {
        //       return $sapeur->id == $id;
        //     }))[0];
        //     $s->display = $sap->nom." ".$sap->prenom;
        //     return $s;
        //   }, array_values($exercice->sapeurs));
          
        return View('pdf/rapport-intervention', ["intervention" => $intervention, "params" => $params]);
        // $pdf = PDF::loadView('pdf/rapport-intervention', ["intervention" => $intervention, "params" => $params]);
        // return $pdf->download('invoice.pdf');
    }
}
