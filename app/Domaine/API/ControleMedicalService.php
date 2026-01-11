<?php

namespace App\Domaine\API;

use App\Domaine\Business\ControleMedicalBusiness;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\ControleMedicalType;
use App\Infrastructure\Models\Medecin;

class ControleMedicalService
{
    protected $business;

    public function __construct(ControleMedicalBusiness $business)
    {
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
        return ControleMedical::all();
    }

    public function getControleMedical($id)
    {
        return ControleMedical::find($id);
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

    public function addJustificatif($controleId, $file, $sisKey)
    {
        return $this->business->addJustificatif($controleId, $file, $sisKey);
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
