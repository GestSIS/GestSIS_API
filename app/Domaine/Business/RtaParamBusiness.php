<?php

namespace App\Domaine\Business;

use App\Infrastructure\Models\RtaParam;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class RtaParamBusiness
{
    public static function updateParams($data)
    {
        // Check validity of the account
        RtaParam::updateOrCreate([], [
            'token' => Crypt::encryptString($data['token']),
        ]);
        return self::getParams();
    }

    public static function getParams()
    {
        try {
            $params = RtaParam::first();
            if (!$params) {
                return [];
            }
            Crypt::decryptString($params->token);

            return [
                'token' => '************************',
            ];
        } catch (DecryptException $e) {
            return [];
        }
    }

}
