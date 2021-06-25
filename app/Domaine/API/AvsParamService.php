<?php

namespace App\Domaine\API;

use App\Domaine\Business\AvsParamBusiness;
use App\Infrastructure\Models\AvsParam;

class AvsParamService
{
    protected $business;

    public function __construct(AvsParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return AvsParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }
}