<?php

namespace CtechPay\Exceptions;

use RuntimeException;
use Throwable;

class CtechPayException extends RuntimeException
{
    public mixed $response;

    public function __construct(string $message, int $code = 0, mixed $response = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
}
