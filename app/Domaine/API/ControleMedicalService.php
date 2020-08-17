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
    
    public function listeAllControlesMedicaux(){}

    public function getControleMedical($id){}
    
    public function addControleMedical($controle){}

    public function deleteControleMedical($id){}

    public function updateControleMedical($controle){}

    public function addFileToControleMedical($file){}

    public function getFileOfControleMedical($file){}

    public function removeFileOfControleMedical($file){}
}