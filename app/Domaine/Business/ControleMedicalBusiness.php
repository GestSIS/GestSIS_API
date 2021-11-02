<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\ControleMedicalRepository;
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
        // TODO: Implémenter supprimer médecin
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
        // TODO: Implémenter supprimer médecin
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
