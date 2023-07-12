<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\AbsenceParam;

class AbsenceParamBusiness
{
    public static function updateParams($data)
    {
        AbsenceParam::updateOrCreate([], $data);
        return AbsenceParam::first();
    }
}
