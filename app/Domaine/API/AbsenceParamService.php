<?php

namespace App\Domaine\API;

use App\Domaine\Business\AbsenceParamBusiness;
use App\Infrastructure\Models\AbsenceParam;

class AbsenceParamService
{
    protected $business;

    public function __construct(AbsenceParamBusiness $business)
    {
        $this->business = $business;
    }

    public function params()
    {
        return AbsenceParam::first();
    }

    public function updateParams($data)
    {
        return $this->business->updateParams($data);
    }
}
