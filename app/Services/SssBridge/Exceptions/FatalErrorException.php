<?php

namespace App\Services\SssBridge\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FatalErrorException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        $message = $message ?: __('Internal server error. Please try again.');

        parent::__construct(500, $message, $previous, $headers, $code);
    }
}
