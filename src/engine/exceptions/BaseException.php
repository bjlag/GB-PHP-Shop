<?php
namespace AppExceptions;

use Exception;

abstract class BaseException extends Exception
{
    public function __construct( string $message = "", int $code = 0, Exception $previous = null )
    {
        parent::__construct( $message, $code, $previous );
    }

    abstract public function getError();
}