<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\AspsmsParam;
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

    private static function utf8_to_gsm0338($string)
    {
        $dict = array(
            '@' => "\x00", '£' => "\x01", '$' => "\x02", '¥' => "\x03", 'è' => "\x04", 'é' => "\x05", 'ù' => "\x06", 'ì' => "\x07", 'ò' => "\x08", 'Ç' => "\x09", 'Ø' => "\x0B", 'ø' => "\x0C", 'Å' => "\x0E", 'å' => "\x0F",
            'Δ' => "\x10", '_' => "\x11", 'Φ' => "\x12", 'Γ' => "\x13", 'Λ' => "\x14", 'Ω' => "\x15", 'Π' => "\x16", 'Ψ' => "\x17", 'Σ' => "\x18", 'Θ' => "\x19", 'Ξ' => "\x1A", 'Æ' => "\x1C", 'æ' => "\x1D", 'ß' => "\x1E", 'É' => "\x1F",
            // all \x2? removed
            // all \x3? removed
            // all \x4? removed
            'Ä' => "\x5B", 'Ö' => "\x5C", 'Ñ' => "\x5D", 'Ü' => "\x5E", '§' => "\x5F",
            '¿' => "\x60",
            'ä' => "\x7B", 'ö' => "\x7C", 'ñ' => "\x7D", 'ü' => "\x7E", 'à' => "\x7F",
            '^' => "\x1B\x14", '{' => "\x1B\x28", '}' => "\x1B\x29", '\\' => "\x1B\x2F", '[' => "\x1B\x3C", '~' => "\x1B\x3D", ']' => "\x1B\x3E", '|' => "\x1B\x40", '€' => "\x1B\x65"
        );
        $converted = strtr($string, $dict);

        // Replace unconverted UTF-8 chars from codepages U+0080-U+07FF, U+0080-U+FFFF and U+010000-U+10FFFF with a single ?
        return preg_replace('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m', '?', $converted);
    }

    public static function send($data)
    {
        $message = self::utf8_to_gsm0338($data['message']);
        $origin = "GestSIS"; // $data['origin']; // Pas pour le moment
        $differe = $data['differe'];
        $date = isset($data['date']) ? $data['date'] : "";
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

            return self::sendTextSMS($username, $password, $message, $origin, $differe, $date, $numeros);
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
                        // Credit insuffisant
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
            throw $e;
            throw new ArrayException([], "Erreur lors de la connexion ASPSMS veuillez contacter votre administrateur system");
        }
        return 'OK';
    }
}
