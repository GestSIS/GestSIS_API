<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JwtTokenValidatorSapeurOrRole
{
    /**
     * Handle an incoming request.
     * Check que l'utilisateur est un sapeur du SIS
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (env('APP_ENV') === 'testing') {
            return $next($request);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé", "test" => $e->__toString()], 401);
        }

        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        if (is_null($sisKey)) {
            return response()->json(["error" => "Sis non sélectionné"], 401);
        }

        // Check is a valid sapeur for the provided sis
        $sapeurs = (array) $token->data->sapeurs;
        $permissions = (array) $token->data->permissions;
        if ($token->data->admin !== True && !array_key_exists($sisKey, $sapeurs) && !array_key_exists($sisKey, $permissions)) {
            return response()->json(["error" => "Votre compte n'est pas lié à un sapeur de ce SIS"], 401);
        }

        if ($token->data->admin === True) {
            $request->attributes->add(['admin' => true]);
        }
        $request->attributes->add(['permissions' => $permissions[$sisKey] ?? []]);
        $request->attributes->add(['sapeurId' => $sapeurs[$sisKey] ?? null]);

        return $next($request);
    }
}
