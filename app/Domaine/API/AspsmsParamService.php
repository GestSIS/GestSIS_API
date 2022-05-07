<?php

namespace App\Domaine\API;

use App\Domaine\Business\AspsmsParamBusiness;
use App\Infrastructure\Models\AspsmsParam;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AspsmsParamService
{
    protected $business;

    public function __construct(AspsmsParamBusiness $business)
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
