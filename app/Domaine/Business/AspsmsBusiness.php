<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\AspsmsParam;
use App\Infrastructure\Models\Sms;
use Carbon\Carbon;
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
            'origin' => isset($data['origin']) ? $data['origin'] : 'GestSIS',
        ]);
        return self::getParams();
    }

    public static function send($data)
    {
        // TODO: A corriger, sans conversion en ascii, apostrophes manquantes
        $message = iconv('UTF-8', 'ASCII//TRANSLIT', $data['message']);
        $origin = "GestSIS"; // $data['origin']; // Pas pour le moment
        $differe = $data['differe'];
        $date = $data['date'] ?? "";
        if ($differe) {
            $date = Carbon::parse($date, "Europe/Zurich")->toIso8601String();
        }

        $numeros = $data['numeros'];

        try {
            $params = AspsmsParam::first();
            if (!$params) {
                return [];
            }
            $username = Crypt::decryptString($params->username);
            $password = Crypt::decryptString($params->password);

            $response = self::sendTextSMS($username, $password, $message, $origin, $differe, $date, $numeros);

            // TODO: Store sent sms in DB
            $sms = new Sms();
            $now = Carbon::now();
            $sms->fill([
                'message' => $message,
                'date_envoie' => $differe ? Carbon::parse($date, "Europe/Zurich") : $now,
                'date_programme' => $now,
                'numeros' => implode(';', $data['numeros']),
                'exercice_id' => $data['exerciceId'] ?? null,
            ]);

            return $response;
        } catch (DecryptException $e) {
            throw new ArrayException([], 'ASPSMS non configuré');
        }
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
            throw new ArrayException([], 'ASPSMS non configuré');
        }
    }

    private static function checkCredit($username, $password)
    {
        try {
            // $response = Http::post('https://json.aspsms.com/ListAllStatusCodes');
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://json.aspsms.com/checkCredits', [
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
                        throw new ArrayException([], "Informations de connexion invalides");
                    case "5":
                        throw new ArrayException([], "Crédit insuffisant");
                    default:
                        throw new ArrayException([], "Erreur ASPSMS veuillez contacter votre administrateur system");
                }
                return $response->body();
            }
        } catch (ConnectionException $e) {
            // throw $e;
            return '?';
        }
        return 0;
    }

    private static function sendTextSMS($username, $password, $message, $origin, $differe, $date, $numeros)
    {
        try {
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
                        return $response;
                        return 'OK';
                    case "2":
                        // Connect failed
                    case "3":
                        // Authorization failed
                        throw new ArrayException([], "Informations de connexion invalides");
                    case "5":
                        // Credit insuffisant
                    default:
                        throw new ArrayException([], "Erreur ASPSMS veuillez contacter votre administrateur system");
                }
                return $response->body();
            }
        } catch (ConnectionException $e) {
            throw new ArrayException([], "Erreur lors de la connexion ASPSMS veuillez contacter votre administrateur system");
        }
        return 'OK';
    }
}
