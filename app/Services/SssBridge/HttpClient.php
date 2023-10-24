<?php

declare(strict_types=1);

namespace App\Services\SssBridge;

use App\Services\SssBridge\Enums\Api;
use Closure;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\{
    Http,
    Log
};
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /**
     * Http client instance
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected HttpFactory $client;

    /**
     * Log the responses
     *
     * @var bool
     */
    protected bool $debug = false;

    /**
     * The base URL
     *
     * @var array
     */
    protected string $baseUrl = '';

    /**
     * The default options
     *
     * @var array
     */
    protected array $options = [];

    /**
     * The API name
     *
     * @var \App\Services\SssBridge\Enums\Api
     */
    protected Api $apiName = Api::Main;

    protected static $callCount = 0;

    public function __construct(string $baseUrl, array $options = [])
    {
        $this->baseUrl = $baseUrl;

        $this->options = array_merge_recursive([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ], $options);

        $this->client = Http::getFacadeRoot();
    }

    public function newRequest(array $headers = [], string $bodyFormat = 'json', string $operation = ''): PendingRequest
    {
        static::$callCount++;

        $eventName = ResponseReceived::class . str_repeat('*', static::$callCount);
        $operation = $operation ?: debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        $headers = array_merge(($this->options['headers'] ?? []) ?: [], $headers);

        Http::getDispatcher()->listen($eventName, function ($eventClass, $events) use ($eventName, $headers, $operation) {
            $event = null;

            foreach ($events as $value) {
                if ($value instanceof ResponseReceived) {
                    $event = $value;
                    break;
                }
            }

            if (! $event) {
                return;
            }

            $request = $event->request;
            $response = $event->response;

            if (! ($response && $request)) {
                return;
            }

            $callingEventName = $response->header('X-Added-Http-Client-Stamp');

            if (! $callingEventName) {
                return;
            }

            $callingEventName = mute_exception(fn () => http_decrypt($callingEventName));

            if (! $callingEventName) {
                return;
            }

            $requestHeaders = [];

            foreach ($request->headers() as $header => $value) {
                if (is_array($value)) {
                    $requestHeaders[$header] = implode('; ', $value);
                } else {
                    $requestHeaders[$header] = $value;
                }
            }

            $responseHeaders = [];

            foreach ($response->headers() as $header => $value) {
                if (is_array($value)) {
                    $responseHeaders[$header] = implode('; ', $value);
                } else {
                    $responseHeaders[$header] = $value;
                }
            }

            $psrRequest = $request->toPsrRequest();
            $uri = $psrRequest->getUri();
            $url = $uri->getPath();
            $message = $response->reason();

            $this->getLogger()->{$response->successful() ? 'info' : 'error'}(
                "[Http@{$operation}] {$message}", [
                    'status' => $response->status(),
                    'method' => $request->method(),
                    'response' => $request->isJson() ? $response->json() : $response->body(),
                    'payload' => $request->data(),
                    'uri' => $url,
                    'response_headers' => $responseHeaders,
                    'request_headers' => $requestHeaders,
                ]
            );

            Http::getDispatcher()->forget($eventName);
        });

        $operation = $operation ?: debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
        $headers = array_merge(($this->options['headers'] ?? []) ?: [], $headers);

        return $this->client
                ->withMiddleware(function ($handler) use ($eventName) {
                    return function (RequestInterface $request, array $options) use ($eventName, $handler) {
                        return $handler($request, $options)
                                ->then(function (ResponseInterface $response) use ($eventName) {
                                    return $response->withAddedHeader('X-Added-Http-Client-Stamp', http_encrypt($eventName));
                                });
                    };
                })
                ->baseUrl($this->baseUrl)
                ->withHeaders($headers)
                ->bodyFormat($bodyFormat)
                ->throw();
    }

    public function __call($method, $parameters)
    {
        return $this->client->{$method}(...$parameters);
    }

    protected function writeLog(
        string $level = 'info',
        string $operation,
        string $message,
        array $payload = [],
        mixed $response = [],
        int $status = 0
    ) {
        $this->getLogger()->{$level}("[Http@{$operation}] {$message}", [
            'status' => $status,
            'payload' => $payload,
            'response' => $response,
        ]);
    }

    public function getLogger()
    {
        return Log::channel($this->debug ? $this->apiName->value : 'null');
    }
}
