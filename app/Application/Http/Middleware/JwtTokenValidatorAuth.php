<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;

class JwtTokenValidatorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('APP_ENV') === 'testing') {
            return $next($request);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé"], 401);
        }

        // Check has role for provided sis
        $perms = (array) $token->data->permissions;
        if (!array_key_exists("_", $perms)) {
            return response()->json(["error" => "Token invalide"], 401);
        }

        if (!in_array("admin", $perms["_"])) {
            return response()->json(["error" => "Permissions insuffisantes"], 401);
        }

        return $next($request);
    }
}
