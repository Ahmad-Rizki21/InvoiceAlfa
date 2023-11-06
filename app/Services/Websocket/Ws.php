<?php

namespace App\Services\Websocket;

use Illuminate\Support\Facades\Http;

class Ws
{
    protected string $url;
    protected string $appSource;

    public function __construct()
    {
        $this->url = config('services.websocket.http_url');
        $this->appSource = config('services.websocket.app_source');
    }

    public function broadcast(string $event, array $data): void
    {
        Http::withQueryParameters([
            'source' => $this->appSource,
            'mode' => 'broadcast',
            'id' => 'bc:' . $event,
            'data' => base64_encode(json_encode($data)),
        ])->get($this->url);
    }

    public function debugServer()
    {
        $response = Http::withQueryParameters([
            'mode' => 'debug',
        ])
        ->withHeader('Origin', 'https://ajnusa-ws.chaynu.dev')
        ->withOptions([
            'debug' => true,
        ])->get($this->url);

        return $response->json();
    }
}
