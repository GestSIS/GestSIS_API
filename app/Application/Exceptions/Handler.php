<?php

namespace App\Application\Exceptions;

use App\Domaine\Exceptions\ArrayException;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ArrayException) {
            return response()->json(['error' => $exception->getErrors()], 200);
        }

        if ($exception instanceof ValidationException) {
            return response()->json(['error' => $exception->errors()], 200);
        }

        return parent::render($request, $exception);
    }
}
