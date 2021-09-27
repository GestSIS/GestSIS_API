<?php

namespace App\Domaine\API;

use App\Infrastructure\Models\ExerciceComptable;
use App\Domaine\Business\ExerciceComptableBusiness;

class ExerciceComptableService
{
    protected $business;

    public function __construct(ExerciceComptableBusiness $business)
    {
        $this->business = $business;
    }

    public function all()
    {
        return ExerciceComptable::all();
    }

    public function creer($data)
    {
        return $this->business->creerExerciceComptable($data);
    }

    public function modifier($id, $data)
    {
        return $this->business->modifierExerciceComptable($id, $data);
    }

    public function supprimer($id)
    {
        return $this->business->supprimerExerciceComptable($id);
    }

    public function cloturer($id)
    {
        return $this->business->cloturerExerciceComptable($id);
    }
}
