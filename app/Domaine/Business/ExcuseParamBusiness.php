<?php

namespace App\Domaine\Business;

use App\Models\ExcuseParam;

class ExcuseParamBusiness
{
    public static function updateParams(array $data): ?ExcuseParam
    {
        $data['texte_email_rappel'] = $data['texte_email_rappel'] ?? '';
        return ExcuseParam::updateOrCreate([], $data);
    }
}
