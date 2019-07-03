<?php


namespace App\Services;


use App\Business\SapeurBusiness;
use App\Contracts\SapeurRepository;

class SapeurService
{
    protected $repository;
    protected $business;

    public function __construct(SapeurRepository $repository, SapeurBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function listeSapeurs()
    {
        return $this->repository->listeSapeurLight();
    }

    public function getSapeurDetailsById($sapeurid)
    {
        return $this->repository->getSapeurDetailsById($sapeurid);
    }

    public function getSapeurGradesById(int $sapeurId)
    {
        return $this->repository->getSapeurGradesById($sapeurId);
    }

    public function getSapeurFonctionsById(int $sapeurId)
    {
        return $this->repository->getSapeurFonctionsById($sapeurId);
    }

    public function getSapeurCoursById(int $sapeurId)
    {
        return $this->repository->getSapeurCoursById($sapeurId);
    }

    public function getSapeurPermisById(int $sapeurId)
    {
        return $this->repository->getSapeurPermisById($sapeurId);
    }

    public function getSapeurMutationsById(int $sapeurId)
    {
        return $this->repository->getSapeurMutationsById($sapeurId);
    }

    public function getSapeurGroupesById(int $sapeurId)
    {
        return $this->repository->getSapeurGroupesbyId($sapeurId);
    }

    public function getSapeurTelephonesById(int $sapeurId)
    {
        return $this->repository->getSapeurTelephonesById($sapeurId);
    }

    public function createSapeur($data)
    {
        return $this->business->createSapeur($data);
    }

    public function editSapeurDetailsById($sapeurId, $data)
    {
        $this->business->updateSapeurById($sapeurId, $data);
        return $this->repository->getSapeurDetailsById($sapeurId);
    }

    public function deleteSapeurById($sapeurId)
    {
        $this->business->deleteSapeurById($sapeurId);
    }

    public function addCours($sapeurId, $cours)
    {
        return $this->business->addCours($sapeurId, $cours);
    }

    public function updateCours($sapeurId, $cours)
    {
        $this->business->updateCours($sapeurId, $cours);
        //TODO Return
    }

    public function removeCours($sapeurId, $coursId)
    {
        $this->business->removeCours($sapeurId, $coursId);
    }

    public function addGrade($sapeurId, $grade)
    {
        return $this->business->addGrade($sapeurId, $grade);
    }

    public function updateGrade($sapeurId, $grade)
    {
        $this->business->updateGrade($sapeurId, $grade);
        //TODO Return
    }

    public function removeGrade($sapeurId, $gradeId)
    {
        $this->business->removeGrade($sapeurId, $gradeId);
    }

    public function addFonction($sapeurId, $fonction)
    {
        return $this->business->addFonction($sapeurId, $fonction);
    }

    public function updateFonction($sapeurId, $fonction)
    {
        $this->business->updateFonction($sapeurId, $fonction);
        //TODO return
    }

    public function removeFonction($sapeurId, $fonctionId)
    {
        $this->business->removeFonction($sapeurId, $fonctionId);
    }

    public function addMutation($sapeurId, $mutation)
    {
        $this->business->addMutation($sapeurId, $mutation);
        //TODO return
    }

    public function updateMutation($sapeurId, $mutation)
    {
        $this->business->updateMutation($sapeurId, $mutation);
        //TODO return
    }

    public function removeMutation($sapeurId, $mutationId)
    {
        $this->business->removeMutation($sapeurId, $mutationId);
    }

    public function addTelephone($sapeurId, $telephone)
    {
        return $this->business->addTelephone($sapeurId, $telephone);
        //TODO return
    }

    public function updateTelephone($sapeurId, $telephone)
    {
        $this->business->updateTelephone($sapeurId, $telephone);
        //TODO return
    }

    public function removeTelephone($sapeurId, $telephoneId)
    {
        $this->business->removeTelephone($sapeurId, $telephoneId);
    }

    public function addPermis($sapeurId, $permis)
    {
        return $this->business->addPermis($sapeurId, $permis);
    }

    public function updatePermis($sapeurId, $permis)
    {
        $this->business->updatePermis($sapeurId, $permis);
    }

    public function removePermis($sapeurId, $permisId)
    {
        $this->business->removePermis($sapeurId, $permisId);
    }
}
