<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class JwtTokenValidatorRole
{
    /**
     * Handle an incoming request.
     * Check que l'utilisateur possède au moins un des droits requis
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
            return response()->json(["error" => "Accès refusé"], 401);
        }

        $sisKey = $request->header('Sis-Key', Null);
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

                $request->attributes->add(['permissions' => $perms[$sisKey] ?? []]);
            }
        }

        if ($token->data->admin === True) {
            $request->attributes->add(['admin' => true]);
        }

        // Récupération du sapeur potentiellement lié
        $sapeurs = (array) $token->data->sapeurs;
        $request->attributes->add(['sapeurId' => $sapeurs[$sisKey] ?? null]);

        return $next($request);
    }
}
