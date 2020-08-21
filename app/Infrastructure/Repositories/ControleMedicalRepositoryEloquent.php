<?php


namespace App\Infrastructure\Repositories;


use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\ControleMedical;
use stdClass;

class ControleMedicalRepositoryEloquent implements ControleMedicalRepository
{
    //TODO Implement this class

    public function listeAllControlesMedicaux()
    {
        $temp = $this;
        return ControleMedical::get()
            ->map(function ($controle) use ($temp) {
                return $temp->convertControleMedical($controle);
            })->toArray();
    }

    public function getControleMedical($id)
    {
        return $this->convertControleMedical(ControleMedical::find($id));
    }

    public function createControleMedical($data)
    {
        if (is_null($data['designation'])) {
            $data['designation'] = '';
        }

        $controle = new ControleMedical();
        $controle->fill($data);
        $controle->sapeur_id = $data['sapeur_id'];
        $controle->save();

        return $this->convertControleMedical($controle);
    }

    public function updateControleMedical($controleId, $data)
    {
        if (is_null($data['designation'])) {
            $data['designation'] = '';
        }

        ControleMedical::where('id', $controleId)->limit(1)->update($data);
        return $this->convertControleMedical(ControleMedical::find($controleId));
    }

    public function deleteControleMedical($id)
    {
        ControleMedical::destroy($id);
    }

    public function addJustificatif($controleMedicalId, $filename, $path)
    {
        $controle = ControleMedical::find($controleMedicalId);
        $controle->filename = $filename;
        $controle->path = $path;
        $controle->save();

        return $this->convertControleMedical($controle);
    }

    public function getJustificatif($id)
    {
        $controle = ControleMedical::find($id); 
        $data = $this->convertControleMedical($controle);
        $data->path = $controle->path;
        return $data;
    }

    public function removeJustificatif($controleMedicalId)
    {
        $controle = ControleMedical::find($controleMedicalId);
        $justificatifPath = $controle->justificatif;
        $controle->filename = null;
        $controle->path = null;
        $controle->save();
        return $justificatifPath;
    }

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
        $object->filename = $controle->filename;

        return $object;
    }
}
