<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\SisParam;

class SisParamBusiness
{
    public static function updateParams($data)
    {
        SisParam::updateOrCreate([], $data);
        return SisParam::first();
    }
}
