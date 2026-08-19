<?php

use App\Application\Http\Middleware\DbSelector;
use App\Application\Http\Middleware\JwtTokenValidatorAuth;
use App\Application\Http\Middleware\JwtTokenValidatorAdmin;
use App\Application\Http\Middleware\JwtTokenValidatorSapeurOrRole;
use App\Application\Http\Middleware\JwtTokenValidatorAny;
use App\Application\Http\Middleware\JwtTokenValidatorRole;
use App\Application\Http\Middleware\JwtTokenValidatorSapeur;
use App\Domaine\Exceptions\InvalidActionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Domaine\Exceptions\ArrayException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwtTokenRole' => JwtTokenValidatorRole::class,
            'jwtTokenAuth' => JwtTokenValidatorAuth::class,
            'jwtTokenadmin' => JwtTokenValidatorAdmin::class,
            'jwtTokenSapeur' => JwtTokenValidatorSapeur::class,
            'jwtTokenSapeurOrRole' => JwtTokenValidatorSapeurOrRole::class,
            'jwtTokenAny' => JwtTokenValidatorAny::class,
            'dbSelector' => DbSelector::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
        $exceptions
            ->render(function (ArrayException $e, Request $request) {
                return response()->json(['error' => $e->getErrors()], 200);
            })
            ->render(function (InvalidActionException $e, Request $request) {
                return response()->json(['error' => $e->getErrors()], 200);
            })
            ->render(function (ValidationException $e, Request $request) {
                return response()->json(['error' => $e->errors()], 200);
            });
    })->create();
