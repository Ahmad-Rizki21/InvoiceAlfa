<?php

namespace App\Services\SssBridge\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FailedOperationException extends HttpException
{
    protected array $response = [];

    protected $payload = [];

    public function __construct(
        string $message = '',
        array $response = [],
        array $payload = [],
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    )
    {
        $message = $message ?: __('The given data requested is invalid.');

        $this->response = $response;

        $this->payload = $payload;

        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getProcessFlag(): ?string
    {
        $response = $this->response;

        if (isset($response['processFlag'])) {
            return (string) $response['processFlag'] ?: null;
        }

        return $response['processFlg'] ?? null;
    }
}
