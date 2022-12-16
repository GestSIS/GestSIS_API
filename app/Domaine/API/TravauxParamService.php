<?php


namespace App\Domaine\API;

use App\Domaine\Business\TravauxParamBusiness;
use App\Infrastructure\Models\TravailType;

class TravauxParamService
{
    protected $repository;
    protected $business;

    public function __construct(TravauxParamBusiness $business)
    {
        $this->business = $business;
    }

    public function travailTypes($avecTarifs)
    {
        if ($avecTarifs) {
            return TravailType::with('fonctions')->get();
        } else {
            return TravailType::all();
        }
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
}
