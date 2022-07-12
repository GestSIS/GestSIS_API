<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Domaine\SPI\ControleMedicalRepository;
use App\Infrastructure\Models\ControleMedical;
use App\Infrastructure\Models\ControleMedicalType;
use App\Infrastructure\Models\Medecin;
use Illuminate\Support\Facades\Storage;

class ControleMedicalBusiness
{
    protected $repository;

    public function __construct(ControleMedicalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function ajouterMedecin($data)
    {
        if (!array_key_exists('adresse', $data) or is_null($data['adresse'])) {
            $data['adresse'] = "";
        }
        $medecin = new Medecin();
        $medecin->fill($data);
        $medecin->save();
        return $medecin;
    }

    public function modifierMedecin($id, $data)
    {
        Medecin::where('id', $id)->limit(1)->update($data);
        return Medecin::find($id);
    }

    public function supprimerMedecin($id)
    {
        if (ControleMedical::where('medecin_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce médecin, celui-ci est utilisé dans un contrôle médical.');
        }
        Medecin::where('id', $id)->delete();
    }

    public function ajouterType($data)
    {
        $type = new ControleMedicalType();
        $type->fill($data);
        $type->save();
        return $type;
    }

    public function modifierType($id, $data)
    {
        ControleMedicalType::where('id', $id)->limit(1)->update($data);
        return ControleMedicalType::find($id);
    }

    public function supprimerType($id)
    {
        if (ControleMedical::where('controle_medical_type_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce type de contrôle médical, celui-ci est utilisé dans un contrôle médical.');
        }
        ControleMedicalType::where('id', $id)->delete();
    }

    public function createControleMedical($controleMedical)
    {
        //TODO Change this
        $controleMedical['en_cours'] = true;
        return $this->repository->createControleMedical($controleMedical);
    }

    public function updateControleMedical($controleId, $controleMedical)
    {
        //TODO Change this
        $controleMedical['en_cours'] = true;
        return $this->repository->updateControleMedical($controleId, $controleMedical);
    }

    public function removeControleMedical($controleId)
    {
        //First remove justificatif
        $this->removeJustificatif($controleId);
        return $this->repository->deleteControleMedical($controleId);
    }

    public function addJustificatif($controleMedicalId, $file, $sisId)
    {
        //First remove potential already existing document
        $this->removeJustificatif($controleMedicalId);

        //Then add the new one
        $path = $file->store('documents/' . $sisId . '/controles_medicaux');
        return $this->repository->addJustificatif($controleMedicalId, $file->getClientOriginalName(), $path);
    }

    public function getJustificatif($controleMedicalId)
    {
        //Return the file
        $justificatif = $this->repository->getJustificatif($controleMedicalId);
        return ['path' => $justificatif->path, 'filename' => $justificatif->filename];
    }

    public function removeJustificatif($controleMedicalId)
    {
        $path = $this->repository->removeJustificatif($controleMedicalId);
        if ($path != null) {
            Storage::delete($path);
        }
    }
}
