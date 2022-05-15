<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\AspsmsParam;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class AspsmsBusiness
{
    public static function updateParams($data)
    {
        // Check validity of the account
        AspsmsParam::updateOrCreate([], [
            'username' => Crypt::encryptString($data['username']),
            'password' => Crypt::encryptString($data['password']),
        ]);
        return self::getParams();
    }

    public static function sendSms($numeros, $message, $date)
    {
        // TODO:
    }

    public static function getParams()
    {
        try {
            $params = AspsmsParam::first();
            if (!$params) {
                return [];
            }
            $username = Crypt::decryptString($params->username);
            $password = Crypt::decryptString($params->password);

            return [
                'username' => $username,
                'password' => '********',
                'credit' => self::checkCredit($username, $password)
            ];
        } catch (DecryptException $e) {
            return [];
        }
    }

    public static function getCredit()
    {
        try {
            $params = AspsmsParam::first();
            if (!$params) {
                return [];
            }
            $username = Crypt::decryptString($params->username);
            $password = Crypt::decryptString($params->password);

            return self::checkCredit($username, $password);
        } catch (DecryptException $e) {
            throw new ArrayException(['message' => 'ASPSMS non configuré']);
        }
    }

    private static function checkCredit($username, $password)
    {
        try {
            // $response = Http::post('https://json.aspsms.com/ListAllStatusCodes');
            $response = Http::post('https://json.aspsms.com/CheckCredits', [
                'UserName' => $username,
                'Password' => $password,
            ]);

            if ($response->successful()) {
                switch ($response['StatusCode']) {
                    case "1":
                        // Valid response
                        $credit = $response['Credits'];
                        return $credit;
                    case "2":
                        // Connect failed
                    case "3":
                        // Authorization failed
                        throw new ArrayException(['message' => "Informations de connexion invalides", $username, $password], "Informations de connexion invalides");
                    case "5":
                        // Credit insuffisant
                    default:
                        throw new ArrayException(['message' => "Erreur ASPSMS veuillez contacter votre administrateur system"], "Erreur ASPSMS veuillez contacter votre administrateur system");
                }
                return $response->body();
            }
        } catch (ConnectionException $e) {
            return 0;
        }
        return 0;
    }
}
