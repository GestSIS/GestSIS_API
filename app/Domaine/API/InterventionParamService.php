<?php

namespace App\Domaine\API;

use App\Domaine\Business\InterventionParamBusiness;
use App\Infrastructure\Models\InterventionTraitement;
use App\Infrastructure\Models\Materiel;
use App\Infrastructure\Models\MissionType;
use App\Infrastructure\Models\StatFederal;
use App\Infrastructure\Models\StatIntervention;
use App\Infrastructure\Models\Telephone;
use App\Infrastructure\Models\TypeIntervention;
use App\Infrastructure\Models\Vehicule;

class InterventionParamService
{
    protected $business;

    public function __construct(InterventionParamBusiness $business)
    {
        $this->business = $business;
    }

    public function stats()
    {
        return StatIntervention::all();
    }

    public function ajouterStat($data)
    {
        return $this->business->ajouterStat($data);
    }

    public function modifierStat($id, $data)
    {
        return $this->business->modifierStat($id, $data);
    }

    public function supprimerStat($id)
    {
        return $this->business->supprimerStat($id);
    }
    
    public function statsFederal()
    {
        return StatFederal::all();
    }

    public function ajouterStatFederal($data)
    {
        return $this->business->ajouterStatFederal($data);
    }

    public function modifierStatFederal($id, $data)
    {
        return $this->business->modifierStatFederal($id, $data);
    }

    public function supprimerStatFederal($id)
    {
        return $this->business->supprimerStatFederal($id);
    }
    
    public function types()
    {
        return TypeIntervention::all();
    }

    public function ajouterType($data)
    {
        return $this->business->ajouterType($data);
    }

    public function modifierType($id, $data)
    {
        return $this->business->modifierType($id, $data);
    }

    public function supprimerType($id)
    {
        return $this->business->supprimerType($id);
    }
    
    public function missions()
    {
        return MissionType::all();
    }

    public function ajouterMission($data)
    {
        return $this->business->ajouterMission($data);
    }

    public function modifierMission($id, $data)
    {
        return $this->business->modifierMission($id, $data);
    }

    public function supprimerMission($id)
    {
        return $this->business->supprimerMission($id);
    }
    
    public function telehpnes()
    {
        return Telephone::all();
    }

    public function ajouterTelephone($data)
    {
        return $this->business->ajouterTelephone($data);
    }

    public function modifierTelephone($id, $data)
    {
        return $this->business->modifierTelephone($id, $data);
    }

    public function supprimerTelephone($id)
    {
        return $this->business->supprimerTelephone($id);
    }
    
    public function vehicules()
    {
        return Vehicule::all();
    }

    public function ajouterVehicule($data)
    {
        return $this->business->ajouterVehicule($data);
    }

    public function modifierVehicule($id, $data)
    {
        return $this->business->modifierVehicule($id, $data);
    }

    public function supprimerVehicule($id)
    {
        return $this->business->supprimerVehicule($id);
    }
    
    public function materiels()
    {
        return Materiel::all();
    }

    public function ajouterMateriel($data)
    {
        return $this->business->ajouterMateriel($data);
    }

    public function modifierMateriel($id, $data)
    {
        return $this->business->modifierMateriel($id, $data);
    }

    public function supprimerMateriel($id)
    {
        return $this->business->supprimerMateriel($id);
    }
    
    public function traitements()
    {
        return InterventionTraitement::all();
    }

    public function ajouterTraitement($data)
    {
        return $this->business->ajouterTraitement($data);
    }

    public function modifierTraitement($id, $data)
    {
        return $this->business->modifierTraitement($id, $data);
    }

    public function supprimerTraitement($id)
    {
        return $this->business->supprimerTraitement($id);
    }
}