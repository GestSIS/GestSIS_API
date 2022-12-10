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

    public function travailTypes()
    {
        return TravailType::all();
    }

    public function ajouterTravailType($data)
    {
        return $this->business->ajouterTravailType($data);
    }

    public function modifierTravailType($id, $data)
    {
        return $this->business->modifierTravailType($id, $data);
    }

    public function supprimerTravailType($id)
    {
        return $this->business->supprimerTravailType($id);
    }
}
