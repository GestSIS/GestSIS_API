<?php

namespace App\Domaine\SPI;

interface ControleMedicalRepository
{
    public function listeAllControlesMedicaux();

    public function getControleMedical($id);

    public function createControleMedical($controle);

    public function deleteControleMedical($id);

    public function updateControleMedical($controleId, $data);

    public function addJustificatif($controleMedicalId, $filename, $path);

    public function getJustificatif($controleMedicalId, $justificatifId);

    public function removeJustificatif($controleMedicalId, $justificatifId);
}
