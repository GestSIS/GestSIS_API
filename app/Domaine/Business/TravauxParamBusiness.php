<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Travail;
use App\Infrastructure\Models\TravailType;
use App\Infrastructure\Models\TravailTypeFonction;

class TravauxParamBusiness
{
    public static function ajouterType($data)
    {
        $type = TravailType::create($data);
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $type->fonctions()->createMany($data['fonctions']);
        $type->fonctions;
        return $type;
    }

    public static function modifierType($id, $data)
    {
        $type = TravailType::find($id);
        $type->update($data);

        $type->fonctions()->delete();
        if (!array_key_exists('fonctions', $data)) {
            $data['fonctions'] = [];
        }
        $type->fonctions()->createMany($data['fonctions']);

        $type->fonctions;
        return $type;
    }

    public static function supprimerType($id)
    {
        if (Travail::where('travail_type_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce travail type, des travaux ont été saisie');
        }
        TravailTypeFonction::where('travail_type_id', $id)->delete();
        TravailType::where('id', $id)->limit(1)->delete();
    }
}
