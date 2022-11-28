<?php

namespace App\Domaine\API;

use App\Infrastructure\Models\CoursSapeur;
use App\Infrastructure\Models\ExerciceComptable;

class CoursService
{
    // protected $repository;
    // protected $repositoryControles;
    // protected $business;

    // public function __construct(SapeurRepository $repository, ControleMedicalRepository $repositoryControles, SapeurBusiness $business)
    // {
    //     $this->repository = $repository;
    //     $this->repositoryControles = $repositoryControles;
    //     $this->business = $business;
    // }

    public function coursSapeurs($exerciceComptableId)
    {
        $exerciceComptable = ExerciceComptable::find($exerciceComptableId);
        if ($exerciceComptable == null) {
            return [];
        }

        return CoursSapeur::with(['cours', 'ecritures'])->where([
            ['date', '>=', $exerciceComptable->debut],
            ['date', '<=', $exerciceComptable->fin],
        ])->orderBy('date')->get();
    }
}
