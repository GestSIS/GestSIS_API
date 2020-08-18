<?php


namespace App\Infrastructure\Repositories;


use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\ControleMedical;
use stdClass;

class ControleMedicalRepositoryEloquent implements ControleMedicalRepository
{
    //TODO Implement this class
    public function listeAllControlesMedicaux(){
        $temp = $this;
        return ControleMedical::with('justificatifs')->get()
            ->map(function ($controle) use ($temp) {
                return $temp->convertControleMedical($controle);
            })->toArray();
    }

    public function getControleMedical($id){
        return $this->convertControleMedical(ControleMedical::with('justificatifs')->find($id));
    }
    
    public function addControleMedical($controle){}

    public function deleteControleMedical($id){}

    public function updateControleMedical($controle){}

    public function addFileToControleMedical($file){}

    public function getFileOfControleMedical($file){}

    public function removeFileOfControleMedical($file){}

    /**
     * @param $controle
     * @return StdClass|null
     */
    protected function convertControleMedical($controle)
    {
        if ($controle == null) return null;

        $object = new StdClass();

        $object->id = $controle->id;
        $object->designation = $controle->designation;
        $object->consultation = $controle->consultation;
        $object->validite = $controle->validite;
        $object->accepter = $controle->accepter;
        $object->en_cours = $controle->en_cours;
        $object->sapeur_id = $controle->sapeur_id;
        $object->medecin_id = $controle->medecin_id;
        $object->controle_medical_type_id = $controle->controle_medical_type_id;
        
        $temp = $this;
        $object->justificatifs = $controle->justificatifs
            ->map(function ($controle) use ($temp) {
                return $temp->convertControleMedical($controle);
            })->toArray();

        return $object;
    }

    /**
     * @param $justificatif
     * @return StdClass|null
     */
    protected function convertJustificatif($justificatif)
    {
        if ($justificatif == null) return null;

        $object = new StdClass();

        $object->id = $justificatif->id;
        $object->filename = $justificatif->filename;
        $object->logicalname = $justificatif->logicalname;
        $object->controle_medical_id = $justificatif->controle_medical_id;

        return $object;
    }

}
