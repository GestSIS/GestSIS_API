<?php

namespace App\Domaine\Business;

use App\Models\ExcuseParam;

class ExcuseParamBusiness
{
    public static function updateParams($data)
    {
        $data['texte_email_rappel'] = $data['texte_email_rappel'] ?? '';
        ExcuseParam::updateOrCreate([], $data);
        return ExcuseParam::first();
    }
}
