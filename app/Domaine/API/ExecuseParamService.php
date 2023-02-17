<?php

namespace App\Domaine\API;

use App\Domaine\Business\SisParamBusiness;
use App\Infrastructure\Models\SisParam;

class SisParamService
{
    protected $business;

    public function __construct(SisParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return SisParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }

    public function ajouterLocalitesSis($data)
    {
        return $this->business->ajouterLocalitesSis($data);
    }

    public function supprimerLocalitesSis($data)
    {
        return $this->business->supprimerLocalitesSis($data);
    }
}
