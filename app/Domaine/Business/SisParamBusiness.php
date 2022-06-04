<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\LocaliteSis;
use App\Infrastructure\Models\SisParam;

class SisParamBusiness
{
    public static function updateParams($data)
    {
        SisParam::updateOrCreate([], $data);
        return SisParam::first();
    }

    public static function ajouterLocalitesSis($data)
    {
        LocaliteSis::insert(array_map(fn ($localite_id) => (['localite_id' => $localite_id]), $data));
        return LocaliteSis::pluck('localite_id')->toArray();
    }

    public static function supprimerLocalitesSis($data)
    {
        LocaliteSis::whereIn('localite_id', $data)->delete();
        return LocaliteSis::pluck('localite_id')->toArray();
    }
}
