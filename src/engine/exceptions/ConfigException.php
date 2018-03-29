<?php
namespace AppExceptions;

use Exception;

class ConfigException extends BaseException
{
    public function __construct( string $message = "", int $code = 0, Exception $previous = null )
    {
        parent::__construct( $message, $code, $previous );
    }

    public function getError()
    {
        return __CLASS__ . ' -> ' . $this->getMessage();
    }
}