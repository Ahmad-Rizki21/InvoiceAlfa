<?php

namespace App\Services\SssBridge\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NoReturnException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        $message = $message ?: __('Failed to get response. No returns received.');

        parent::__construct(500, $message, $previous, $headers, $code);
    }
}
