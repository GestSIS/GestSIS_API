<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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
        if (App::environment('testing')) {
            return $next($request);
        }

        try {
            $token = TokenTools::validateToken($request->bearerToken());
        } catch (Exception $e) {
            return response()->json(["error" => "Accès refusé", "test" => $e->__toString()], 401);
        }

        $sisKey = $request->header('Sis-Key', Null);
        if (is_null($sisKey)) {
            return response()->json(["error" => "Sis non sélectionné"], 401);
        }

        // Check is a valid sapeur for the provided sis, or has one of the required roles
        $sapeurs = (array) $token->data->sapeurs;
        $permissions = (array) $token->data->permissions;
        if ($token->data->admin !== True && !array_key_exists($sisKey, $sapeurs)) {
            if (!array_key_exists($sisKey, $permissions)) {
                return response()->json(["error" => "Votre compte n'est pas lié à un sapeur de ce SIS"], 401);
            }

            if (count($roles) > 0 && count(array_intersect($roles, (array) $permissions[$sisKey])) == 0) {
                return response()->json(["error" => "Au moins 1 des rôles suivant est requis [" . join(", ", $roles) . "]."], 401);
            }
        }

        if ($token->data->admin === True) {
            $request->attributes->add(['admin' => true]);
        }
        $request->attributes->add(['permissions' => $permissions[$sisKey] ?? []]);
        $request->attributes->add(['sapeurId' => $sapeurs[$sisKey] ?? null]);

        return $next($request);
    }
}
