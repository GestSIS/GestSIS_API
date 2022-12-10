<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\AspsmsParam;
use App\Infrastructure\Models\Travail;
use App\Infrastructure\Models\TravailType;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class TravauxParamBusiness
{

    public function ajouterTravailType($data)
    {
        $travailType = new TravailType();
        $travailType->fill($data);
        $travailType->save();
        return $travailType;
    }

    public function modifierTravailType($id, $data)
    {
        TravailType::where('id', $id)->limit(1)->update($data);
        return TravailType::find($id);
    }

    public function supprimerTravailType($id)
    {
        if (Travail::where('travail_type_id', '=', $id)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer ce travail type, des travaux ont été saisie');
        }
        TravailType::where('id', $id)->delete();
    }
}
