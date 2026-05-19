<?php

namespace App\Domaine\Business;

use App\Models\AbsenceParam;

class AbsenceParamBusiness
{
    /**
     * Update absence parameters
     *
     * @param array<string, mixed> $data
     * @return AbsenceParam
     */
    public static function updateParams(array $data): AbsenceParam
    {
        return AbsenceParam::updateOrCreate([], $data);
    }
}
