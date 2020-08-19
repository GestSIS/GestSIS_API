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
        //TODO Remove all justificatifs
        return $this->repository->deleteControleMedical($controleId);
    }

    public function addJustificatif($controleMedicalId, $file)
    {
        //TODO do the following
        $path = $file->store('documents/justificatifs');
        return $this->repository->addJustificatif($controleMedicalId, $file->getClientOriginalName(), $path);
    }

    public function getJustificatif($controleMedicalId, $justificatifId)
    {
        //TODO do the following
        //return the file
        $justificatif = $this->repository->getJustificatif($controleMedicalId, $justificatifId);
        return $justificatif->logicalname;
    }

    public function removeJustificatif($controleMedicalId, $justificatifId)
    {
        //TODO assert both id are related
        //Remove file
        //Remove reccord
        // Storage::disk('s3')->delete('folder_path/file_name.jpg');
    }
}
