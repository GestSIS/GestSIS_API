<?php

namespace App\Domaine\API;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Domaine\SPI\ControleMedicalRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Exercice;
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

    public function getJustificatif($controleId, $id)
    {
        return $this->business->getJustificatif($controleId, $id);
    }

    public function removeJustificatif($controleId, $id)
    {
        return $this->business->removeJustificatif($controleId, $id);
    }
}
