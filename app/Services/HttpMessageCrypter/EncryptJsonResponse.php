<?php

declare(strict_types=1);

namespace App\Services\HttpMessageCrypter;

use Closure;
use Illuminate\Http\JsonResponse as BaseJsonResponse;

class EncryptJsonResponse
{
    /**
     * Encypter instance.
     *
     * @ \App\Services\HttpMessageCrypter\Encrypter
     */
    protected $encrypter;

    /**
     * Instantiate middleware
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (
            config('http-message-crypter.enabled') &&
            $request->expectsJson() &&
            $response instanceof BaseJsonResponse &&
            ! ($response instanceof JsonResponse)
        ) {
            try {
                $payload = json_encode($response->getData(true));

                $encrypted = $this->encrypter->encrypt($payload);

                $response->setData($encrypted);
            } catch (\Throwable $e) {}
        }

        return $response;
    }
}
