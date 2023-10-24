<?php

namespace App\Services\SecureHeaders;

use Illuminate\Support\ServiceProvider;

class SecureHeadersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/secure-headers.php', 'secure-headers');
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/config/secure-headers.php' => config_path('secure-headers.php'),
        ], 'config');
    }
}
