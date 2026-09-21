<?php

namespace App\Domaine\Exceptions;

use Exception;
use Throwable;

class InvalidActionException extends Exception
{
    private $errors;
    private int $status;

    public function __construct(
        $errors = [],
        $message = "",
        $code = 0,
        ?Throwable $previous = null,
        int $status = 422
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
        $this->status = $status;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
