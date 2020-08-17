<?php


namespace App\Infrastructure\Repositories;


use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\Compte;
use stdClass;

class ControleMedicalRepositoryEloquent implements ControleMedicalRepository
{
    //TODO Implement this class
    public function listeAllControlesMedicaux(){}

    public function getControleMedical($id){}
    
    public function addControleMedical($controle){}

    public function deleteControleMedical($id){}

    public function updateControleMedical($controle){}

    public function addFileToControleMedical($file){}

    public function getFileOfControleMedical($file){}

    public function removeFileOfControleMedical($file){}

    // public function listComptes()
    // {
    //     $temp = $this;
    //     return Compte::all()
    //         ->map(function ($compte) use ($temp) {
    //             return $temp->convertCompte($compte);
    //         })->toArray();
    // }

    // /**
    //  * @param $compte
    //  * @return StdClass|null
    //  */
    // protected function convertCompte($compte)
    // {
    //     if ($compte == null) return null;

    //     $object = new StdClass();

    //     $object->id = $compte->id;
    //     $object->numero = $compte->numero;
    //     $object->designation = $compte->designation;

    //     return $object;
    // }

}
