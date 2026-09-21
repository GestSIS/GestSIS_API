<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class JwtTokenValidatorAuth
{
    /**
     * Handle an incoming request.
     * Check que l'utilisateur est authentifié dans le système
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (App::environment('testing')) {
            return $next($request);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["message" => "Accès refusé --"], 401);
        }

        // Check has role for provided sis
        $perms = (array) $token->data->permissions;
        if (!array_key_exists("_", $perms)) {
            return response()->json(["message" => "Token invalide"], 401);
        }

        if (!in_array("admin", $perms["_"])) {
            return response()->json(["message" => "Permissions insuffisantes"], 401);
        }

        return $next($request);
    }
}
