<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;

class JwtTokenValidatorRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (env('APP_ENV') === 'testing') {
            return $next($request);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé"], 401);
        }

        $sisKey = $request->header('Sis-Id', Null);
        if (is_null($sisKey)) {
            return response()->json(["error" => "Sis non sélectionné"], 401);
        }

        if (count($roles) > 0) {
            if ($token->data->admin !== True) {
                // Check has role for provided sis
                $perms = (array) $token->data->permissions;
                if (!array_key_exists($sisKey, $perms)) {
                    return response()->json(["error" => "Aucun droit pour ce sis"], 401);
                }

                if (count(array_intersect($roles, $perms[$sisKey])) == 0) {
                    return response()->json(["error" => "Au moins 1 des rôles suivant est requis [" . join(", ", $roles) . "]."], 401);
                }
            }
        }

        return $next($request);
    }
}
