<?php

namespace App\Domaine\Business;

use App\Models\ConvocationParam;

class ConvocationParamBusiness
{
    public static function updateParams($data)
    {
        $data['titre'] ??= '';
        $data['texte_debut'] ??= '';
        $data['texte_fin'] ??= '';
        $data['texte_pour_info'] ??= '';
        ConvocationParam::updateOrCreate([], $data);
        return ConvocationParam::first();
    }
}
