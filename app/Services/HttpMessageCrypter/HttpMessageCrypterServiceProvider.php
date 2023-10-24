<?php

namespace App\Services\HttpMessageCrypter;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HttpMessageCrypterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/http-message-crypter.php', 'http-message-crypter');

        $this->app->singleton(Encrypter::class, fn () => new Encrypter());

        $this->app->rebinding('request', function ($app, $request) {
            if (
                $request instanceof Request &&
                $request->expectsJson() &&
                $request->filled('__') &&
                $request->__
            ) {
                $encrypter = $app->make(Encrypter::class);

                if ($encrypter->isEncrypted($request->__) && is_string(($payload = $encrypter->decrypt($request->__)))) {
                    $payload = json_decode($payload, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $request->merge($payload);
                    }
                }

                $request->offsetUnset('__');
            }
        });
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/config/http-message-crypter.php' => config_path('http-message-crypter.php'),
        ], 'config');
    }
}
