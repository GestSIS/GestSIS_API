<?php

namespace App\Domaine\SPI;

interface ControleMedicalRepository
{
    public function listeAllControlesMedicaux();

    public function getControleMedical($id);

    public function createControleMedical($controle);

    public function deleteControleMedical($id);

    public function updateControleMedical($controleId, $data);

    public function getJustificatif($id);

    public function addJustificatif($controleMedicalId, $path, $filename);

    public function removeJustificatif($controleMedicalId);
}
