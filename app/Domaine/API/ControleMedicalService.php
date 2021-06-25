<?php

namespace App\Domaine\API;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Domaine\SPI\ControleMedicalRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\ControleMedicalType;
use App\Infrastructure\Models\Exercice;
use App\Infrastructure\Models\Medecin;
use Illuminate\Database\Eloquent\Collection;

class ControleMedicalService
{
    protected $repository;
    protected $business;

    public function __construct(ControleMedicalRepository $repository, ControleMedicalBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }
    
    public function medecins()
    {
        return Medecin::all();
    }

    public function ajouterMedecin($data)
    {
        return $this->business->ajouterMedecin($data);
    }

    public function modifierMedecin($id, $data)
    {
        return $this->business->modifierMedecin($id, $data);
    }
    
    public function supprimerMedecin($id)
    {
        return $this->business->supprimerMedecin($id);
    }
        
    public function types()
    {
        return ControleMedicalType::all();
    }

    public function ajouterType($data)
    {
        return $this->business->ajouterType($data);
    }

    public function modifierType($id, $data)
    {
        return $this->business->modifierType($id, $data);
    }
    
    public function supprimerType($id)
    {
        return $this->business->supprimerType($id);
    }

    public function listeAllControlesMedicaux()
    {
        return $this->repository->listeAllControlesMedicaux();
    }

    public function getControleMedical($id)
    {
        return $this->repository->getControleMedical($id);
    }

    public function createControleMedical($controle)
    {
        return $this->business->createControleMedical($controle);
    }

    public function updateControleMedical($controleId, $controle)
    {
        return $this->business->updateControleMedical($controleId, $controle);
    }

    public function deleteControleMedical($id)
    {
        return $this->business->removeControleMedical($id);
    }

    public function addJustificatif($controleId, $file)
    {
        return $this->business->addJustificatif($controleId, $file);
    }

    public function getJustificatif($controleId)
    {
        return $this->business->getJustificatif($controleId);
    }

    public function removeJustificatif($controleId)
    {
        return $this->business->removeJustificatif($controleId);
    }
}
