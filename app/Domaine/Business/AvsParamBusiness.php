<?php

namespace App\Domaine\Business;

use App\Models\AvsParam;

class AvsParamBusiness
{
    public static function updateParams($data)
    {
        AvsParam::updateOrCreate([], $data);
        return AvsParam::first();
    }
}
