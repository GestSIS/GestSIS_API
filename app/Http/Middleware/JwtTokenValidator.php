<?php

namespace App\Http\Middleware;

use Closure;
use App\Auth\TokenTools;
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
        try{
            TokenTools::validateToken($request->bearerToken());
        }catch(Exception $e){
            return response()->json(["error" => "access refused"], 401);
        }

        return $next($request);
    }
}
