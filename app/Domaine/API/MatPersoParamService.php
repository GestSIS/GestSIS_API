<?php


namespace App\Domaine\API;

use App\Domaine\Business\MatPersoParamBusiness;
use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\MaterielAlerteType;
use App\Infrastructure\Models\MaterielCategorie;
use App\Infrastructure\Models\MaterielEvent;
use App\Infrastructure\Models\MaterielEventType;
use App\Infrastructure\Models\MaterielType;

class MatPersoParamService
{
    protected $business;

    public function __construct(MatPersoParamBusiness $business)
    {
        $this->business = $business;
    }

    // Categories
    public function categories()
    {
        return MaterielCategorie::get();
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

    // Types
    public function types()
    {
        return MaterielType::get();
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


    // Alertes types
    public function alertes()
    {
        return MaterielAlerteType::with('eventTypes')->get();
    }

    public function ajouterAlerteType($data)
    {
        return $this->business->ajouterAlerteType($data);
    }

    public function modifierAlerteType($id, $data)
    {
        return $this->business->modifierAlerteType($id, $data);
    }

    public function supprimerAlerteType($id)
    {
        return $this->business->supprimerAlerteType($id);
    }

    // Events types
    public function events()
    {
        return MaterielEventType::with('materielTypes')->get();
    }

    public function ajouterEventType($data)
    {
        return $this->business->ajouterEventType($data);
    }

    public function modifierEventType($id, $data)
    {
        return $this->business->modifierEventType($id, $data);
    }

    public function supprimerEventType($id)
    {
        return $this->business->supprimerEventType($id);
    }
}
