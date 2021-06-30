<?php

namespace App\Domaine\API;

use App\Domaine\Business\SapeurParamBusiness;
use App\Infrastructure\Models\Cours;
use App\Infrastructure\Models\Fonction;
use App\Infrastructure\Models\Grade;
use App\Infrastructure\Models\Groupe;

class SapeurParamService
{
    protected $business;

    public function __construct(SapeurParamBusiness $business)
    {
        $this->business = $business;
    }

    public function grades()
    {
        return Grade::all();
    }

    public function ajouterGrade($data)
    {
        return $this->business->ajouterGrade($data);
    }

    public function modifierGrade($id, $data)
    {
        return $this->business->modifierGrade($id, $data);
    }

    public function supprimerGrade($id)
    {
        return $this->business->supprimerGrade($id);
    }

    public function fonctions()
    {
        return Fonction::all();
    }

    public function ajouterFonction($data)
    {
        return $this->business->ajouterFonction($data);
    }

    public function modifierFonction($id, $data)
    {
        return $this->business->modifierFonction($id, $data);
    }

    public function supprimerFonction($id)
    {
        return $this->business->supprimerFonction($id);
    }

    public function cours()
    {
        return Cours::all();
    }

    public function ajouterCours($data)
    {
        return $this->business->ajouterCours($data);
    }

    public function modifierCours($id, $data)
    {
        return $this->business->modifierCours($id, $data);
    }

    public function supprimerCours($id)
    {
        return $this->business->supprimerCours($id);
    }

    public function groupes()
    {
        return Groupe::all();
    }

    public function ajouterGroupe($data)
    {
        return $this->business->ajouterGroupe($data);
    }

    public function modifierGroupe($id, $data)
    {
        return $this->business->modifierGroupe($id, $data);
    }

    public function supprimerGroupe($id)
    {
        return $this->business->supprimerGroupe($id);
    }
}
