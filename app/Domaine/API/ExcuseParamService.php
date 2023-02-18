<?php

namespace App\Domaine\API;

use App\Domaine\Business\ExcuseParamBusiness;
use App\Infrastructure\Models\ExcuseParam;

class ExcuseParamService
{
    protected $business;

    public function __construct(ExcuseParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return ExcuseParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }

    public function ajouterLocalitesExcuse($data)
    {
        return $this->business->ajouterLocalitesExcuse($data);
    }

    public function supprimerLocalitesExcuse($data)
    {
        return $this->business->supprimerLocalitesExcuse($data);
    }
}
