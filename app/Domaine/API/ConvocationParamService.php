<?php

namespace App\Domaine\API;

use App\Domaine\Business\ConvocationParamBusiness;
use App\Infrastructure\Models\ConvocationParam;

class ConvocationParamService
{
    protected $business;

    public function __construct(ConvocationParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return ConvocationParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }
}
