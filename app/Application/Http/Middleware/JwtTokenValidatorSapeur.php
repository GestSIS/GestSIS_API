<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;

class JwtTokenValidatorSapeur
{
    /**
     * Handle an incoming request.
     * Check que l'utilisateur est un sapeur du SIS
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

        // Check is a valid sapeur for the provided sis
        $sapeurs = (array) $token->data->sapeurs;
        if (!array_key_exists($sisKey, $sapeurs)) {
            return response()->json(["error" => "Votre compte n'est pas lié à un sapeur de ce SIS"], 401);
        }
        $request->attributes->add(['sapeurId' => $sapeurs[$sisKey]]);

        return $next($request);
    }
}
