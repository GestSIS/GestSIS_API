<?php

namespace App\Application\Http\Middleware;

use Closure;
use App\Application\Auth\TokenTools;
use Exception;

class JwtTokenValidator
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

        try{
            TokenTools::validateToken($request->bearerToken());
        }catch(Exception $e){
            return response()->json(["error" => "access refused"], 401);
        }

        return $next($request);
    }
}
