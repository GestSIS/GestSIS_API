<?php

namespace App\Domaine\API;

use App\Domaine\Business\ExerciceParamBusiness;
use App\Infrastructure\Models\ExcuseType;
use App\Infrastructure\Models\ExerciceCategorie;
use App\Infrastructure\Models\HeureExerciceType;

class ExerciceParamService
{
    protected $business;

    public function __construct(ExerciceParamBusiness $business)
    {
        $this->business = $business;
    }

    public function categories()
    {
        return ExerciceCategorie::all();
    }

    public function ajouterCategorie($data)
    {
        return $this->business->ajouterCategorie($data);
    }

    public function modifierCategorie($id, $data)
    {
        return $this->business->modifierCategorie($id, $data);
    }

    public function supprimerCategorie($id)
    {
        return $this->business->supprimerCategorie($id);
    }

    public function excusesTypes()
    {
        return ExcuseType::all();
    }

    public function ajouterExcuseType($data)
    {
        return $this->business->ajouterExcuseType($data);
    }

    public function modifierExcuseType($id, $data)
    {
        return $this->business->modifierExcuseType($id, $data);
    }

    public function supprimerExcuseType($id)
    {
        return $this->business->supprimerExcuseType($id);
    }

    public function heuresExerciceType()
    {
        return HeureExerciceType::all();
    }

    public function ajouterHeureExerciceType($data)
    {
        return $this->business->ajouterHeureExerciceType($data);
    }

    public function modifierHeureExerciceType($id, $data)
    {
        return $this->business->modifierHeureExerciceType($id, $data);
    }

    public function supprimerHeureExerciceType($id)
    {
        return $this->business->supprimerHeureExerciceType($id);
    }
}
