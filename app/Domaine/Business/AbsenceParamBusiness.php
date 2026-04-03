<?php

namespace App\Domaine\Business;

use App\Models\AbsenceParam;

class AbsenceParamBusiness
{
    public static function updateParams($data)
    {
        AbsenceParam::updateOrCreate([], $data);
        return AbsenceParam::first();
    }
}
