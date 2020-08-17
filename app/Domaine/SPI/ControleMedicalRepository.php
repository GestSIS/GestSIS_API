<?php
namespace App\Domaine\SPI;

interface ControleMedicalRepository
{
    public function listeAllControlesMedicaux();

    public function getControleMedical($id);
    
    public function addControleMedical($controle);

    public function deleteControleMedical($id);

    public function updateControleMedical($controle);

    public function addFileToControleMedical($file);

    public function getFileOfControleMedical($file);

    public function removeFileOfControleMedical($file);
}
