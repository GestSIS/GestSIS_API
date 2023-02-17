<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\ExcuseParam;

class ExcuseParamBusiness
{
    public static function updateParams($data)
    {
        ExcuseParam::updateOrCreate([], $data);
        return ExcuseParam::first();
    }
}
