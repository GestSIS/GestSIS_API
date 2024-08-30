<?php

namespace App\Domaine\API;

use App\Domaine\Business\AspsmsBusiness;

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
