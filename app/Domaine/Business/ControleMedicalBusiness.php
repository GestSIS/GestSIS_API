<?php

namespace App\Domaine\Business;

use App\Domaine\SPI\ControleMedicalRepository;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Intervention;
use App\Infrastructure\Models\Justificatif;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ControleMedicalBusiness
{

    protected $repository;

    public function __construct(ControleMedicalRepository $repository)
    {
        $this->repository = $repository;
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

    public function addJustificatif($controleMedicalId, $file)
    {
        //First remove potential already existing document
        $this->removeJustificatif($controleMedicalId);

        //Then add the new one
        $path = $file->store('documents/controles_medicaux');
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
