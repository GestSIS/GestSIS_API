<?php


namespace App\Infrastructure\Repositories;


use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\ControleMedicalType;
use App\Infrastructure\Models\Justificatif;
use stdClass;

class ControleMedicalRepositoryEloquent implements ControleMedicalRepository
{
    //TODO Implement this class

    public function listeAllControlesMedicaux()
    {
        $temp = $this;
        return ControleMedical::with('justificatifs')->get()
            ->map(function ($controle) use ($temp) {
                return $temp->convertControleMedical($controle);
            })->toArray();
    }

    public function getControleMedical($id)
    {
        return $this->convertControleMedical(ControleMedical::with('justificatifs')->find($id));
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
        $justificatif = new Justificatif();
        $justificatif->controle_medical_id = $controleMedicalId;
        $justificatif->filename = $filename;
        $justificatif->logicalname = $path;
        $justificatif->save();

        return $this->convertJustificatif($justificatif);
    }

    public function getJustificatif($controleMedicalId, $justificatifId)
    {
        return $this->convertJustificatif(Justificatif::where([['controle_medical_id','=',$controleMedicalId],['id', '=', $justificatifId]])->first());
    }

    public function removeJustificatif($controleMedicalId, $justificatifId)
    {
        ControleMedical::where([['controle_medical_id','=',$controleMedicalId],['id', '=', $justificatifId]])->destroy();
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

        $justificatifs = array();
        foreach ($controle->justificatifs as $j) {
            array_push($justificatifs, $this->convertJustificatif($j));
        }
        $object->justificatifs = $justificatifs;

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
