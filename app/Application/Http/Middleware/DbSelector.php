<?php

namespace App\Application\Http\Middleware;

use App\Support\Sis;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DbSelector
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (App::environment('testing')) {
            return $next($request);
        }

        $sisKey = $request->header('Sis-Key', Null);
        if (is_null($sisKey)) {
            return response()->json(["error" => "Sis non sélectionné"], 401);
        }

        if (!Sis::isValid($sisKey)) {
            return response()->json(["error" => "Sis inconnu"], 401);
        }

        Sis::use($sisKey);
        return $next($request);
    }
}
