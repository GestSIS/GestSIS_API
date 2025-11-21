<?php

namespace App\Domaine\API;

use App\Domaine\Business\RtaParamBusiness;

class RtaParamService
{
    protected $business;

    public function __construct(RtaParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return $this->business->getParams();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }
}
