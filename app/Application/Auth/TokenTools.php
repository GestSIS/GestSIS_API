<?php


namespace App\Application\Auth;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Stdclass;
use Illuminate\Support\Facades\Storage;

class TokenTools
{
    private const ACCESS_TOKEN_DURATION_IN_HOURS = 24;
    private const REFRESH_TOKEN_DURATION_IN_DAYS = 30;

    private const REFRESH_TOKEN_LENGTH = 16;

    private const ISSUER = "GestSIS_Auth";
    private const AUDIENCE = "GestSIS_API";

    private const PRIVATE_KEY_FILE = "auth-private.key";
    private const PUBLIC_KEY_FILE = "auth-public.key";

    /**
     * @param $user
     * @return string
     * @throws FileNotFoundException
     */
    public static function createAccessToken($user)
    {
        //        Log::debug("CREATE TOKEN");

        $privateKey = Storage::disk('keys')->get(self::PRIVATE_KEY_FILE);

        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 0; //not before in seconds
        $expire_claim = $issuedat_claim + self::ACCESS_TOKEN_DURATION_IN_HOURS * 3600; // expire time in seconds

        $token = array(
            "iss" => self::ISSUER,
            "aud" => self::AUDIENCE,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $user->id,
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
                "email" => $user->email
            )
        );

        return JWT::encode($token, $privateKey, 'RS256');
    }

    /**
     * @return Stdclass $token
     */
    public static function createRefreshToken()
    {
        //        Log::debug("CREATE REFRESH TOKEN");

        //Generate a random string.
        $token = Str::random(self::REFRESH_TOKEN_LENGTH);

        //Convert the binary data into hexadecimal representation.
        $refreshToken = new Stdclass();
        $refreshToken->token = bin2hex($token);
        $refreshToken->expire = Carbon::now()->addDays(self::REFRESH_TOKEN_DURATION_IN_DAYS);
        return $refreshToken;
    }

    /**
     * @param $token
     * @return object
     * @throw ExpiredException
     * @throws FileNotFoundException
     */
    public static function validateToken($token)
    {
        //        Log::debug("VALIDATE TOKEN");
        $publicKey = Storage::disk('keys')->get(self::PUBLIC_KEY_FILE);

        return JWT::decode($token, new Key($publicKey, 'RS256'));
    }
}
