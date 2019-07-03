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

    public function createSapeur($sapeurId, $data)
    {
        return $this->business->createSapeur($sapeurId, $data);
    }

    public function editSapeurDetailsById($sapeurId, $data)
    {
        return $this->business->editSapeurDetailsById($sapeurId, $data);
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
        return $this->business->updateCours($sapeurId, $cours);
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
        return $this->business->updateGrade($sapeurId, $grade);
    }

    public function removeGrade($sapeurId, $gradeId)
    {
        $this->business->removeGrade($sapeurId, $gradeId);
    }

    public function addFonction($sapeurId, $fonction)
    {
        $this->business->addFonction($sapeurId, $fonction);
    }

    public function updateFonction($sapeurId, $fonction)
    {
        $this->business->updateFonction($sapeurId, $fonction);
    }

    public function removeFonction($sapeurId, $fonctionId)
    {
        $this->business->removeFonction($sapeurId, $fonctionId);
    }

    public function addMutation($sapeurId, $mutation)
    {
        $this->business->addMutation($sapeurId, $mutation);
    }

    public function updateMutation($sapeurId, $mutation)
    {
        $this->business->updateMutation($sapeurId, $mutation);
    }

    public function removeMutation($sapeurId, $mutationId)
    {
        $this->business->removeMutation($sapeurId, $mutationId);
    }

    public function addTelephone($sapeurId, $telephone)
    {
        $this->business->addTelephone($sapeurId, $telephone);
    }

    public function updateTelephone($sapeurId, $telephone)
    {
        $this->business->updateTelephone($sapeurId, $telephone);
    }

    public function removeTelephone($sapeurId, $telephoneId)
    {
        $this->business->removeTelephone($sapeurId, $telephoneId);
    }

    public function addPermis($sapeurId, $permis)
    {
        $this->business->addPermis($sapeurId, $permis);
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
