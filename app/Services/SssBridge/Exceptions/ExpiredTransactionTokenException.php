<?php

namespace App\Services\SssBridge\Exceptions;

use App\Services\SssBridge\Enums\Api;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExpiredTransactionTokenException extends HttpException
{
    protected Api $api;

    public function __construct(Api $api, \Throwable $previous = null, array $headers = [], int $code = 0)
    {
        $message = __('Forbidden.');

        $this->api = $api;

        parent::__construct(403, $message, $previous, $headers, $code);
    }

    public function getApi()
    {
        return $this->api;
    }
}
