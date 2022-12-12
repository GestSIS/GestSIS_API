<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\Travail;
use App\Infrastructure\Models\TravailType;

class TravauxParamBusiness
{

    public function ajouterType($data)
    {
        $travailType = new TravailType($data);
        $travailType->save();
        return $travailType;
    }

    public function modifierType($id, $data)
    {
        TravailType::where('id', $id)->limit(1)->update($data);
        return TravailType::find($id);
    }

    public function supprimerType($id)
    {
        if (Travail::where('travail_type_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce travail type, des travaux ont été saisie');
        }
        TravailType::where('id', $id)->delete();
    }
}
