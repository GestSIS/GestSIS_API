<?php

namespace App\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

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

        if (!in_array($sisKey, config('database.dbs'), true)) {
            return response()->json(["error" => "Sis inconnu"], 401);
        }

        Config::set('database.default', 'db_' . $sisKey);
        return $next($request);
    }
}
