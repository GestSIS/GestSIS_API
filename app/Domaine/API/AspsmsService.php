<?php

namespace App\Domaine\API;

use App\Domaine\Business\AspsmsBusiness;
use App\Infrastructure\Models\AspsmsParam;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AspsmsService
{
    protected $business;

    public function __construct(AspsmsBusiness $business)
    {
        $this->business = $business;
    }

    public function credit()
    {
        return $this->business->getCredit();
    }

    public function send($data)
    {
        return $this->business->send($data);
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
