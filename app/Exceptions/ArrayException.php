<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ArrayException extends Exception
{
    private $errors;

    public function __construct($errors, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
