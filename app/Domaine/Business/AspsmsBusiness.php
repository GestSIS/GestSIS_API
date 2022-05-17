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

    public static function send($data)
    {
        $message = $data['message'];
        $origin = "GestSIS"; // $data['origin']; // Pas pour le moment
        $differe = $data['differe'];
        $date = isset($data['date']) ? $data['date'] : "";
        $numeros = $data['numeros'];

        try {
            $params = AspsmsParam::first();
            if (!$params) {
                return [];
            }
            $username = Crypt::decryptString($params->username);
            $password = Crypt::decryptString($params->password);

            self::sendTextSMS($username, $password, $message, $origin, $differe, $date, $numeros);
        } catch (DecryptException $e) {
            throw new ArrayException(['message' => 'ASPSMS non configuré']);
        }

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


    private static function sendTextSMS($username, $password, $message, $origin, $differe, $date, $numeros)
    {
        try {
            // $response = Http::post('https://json.aspsms.com/ListAllStatusCodes');
            $response = Http::post('https://json.aspsms.com/SendTextSMS', [
                'UserName' => $username,
                'Password' => $password,
                'Originator' => $origin,
                'Recipients' => $numeros,
                'MessageText' => $message,
                'DeferredDeliveryTime' => $differe ? $date : NULL,
                'FlashingSMS' => false,
                'URLBufferedMessageNotification' => NULL,
                'URLDeliveryNotification' => NULL,
                'URLNonDeliveryNotification' => NULL,
                'AffiliateID' => NULL,
            ]);

            if ($response->successful()) {
                switch ($response['StatusCode']) {
                    case "1":
                        // Valid response
                        $res = $response['Credits'];
                        return $res;
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
