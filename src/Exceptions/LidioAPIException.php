<?php

namespace Shotwn\LidioAPI\Exceptions;

/**
 * This is the root exception for all Lidio API exceptions.
 */
class LidioAPIException extends \Exception
{
    public function __construct($message, $code = 0, \Throwable $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
