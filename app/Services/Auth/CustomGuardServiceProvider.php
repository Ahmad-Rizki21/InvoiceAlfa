<?php

namespace App\Services\Auth;

use App\Services\SssBridge\Enums\Api;
use Carbon\Carbon;
use Illuminate\Auth\RequestGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
// use Laravel\Sanctum\Console\Commands\PruneExpired;
use Laravel\Sanctum\Sanctum;

class CustomGuardServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Sanctum::$personalAccessTokenModel = config('sanctum.personal_access_token_model');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (app()->runningInConsole()) {
        //     $this->commands([
        //         PruneExpired::class,
        //     ]);
        // }

        $this->configureGuard();
    }

    /**
     * Configure the Sanctum authentication guard.
     *
     * @return void
     */
    protected function configureGuard()
    {
        // Auth::extend('custom', function ($app, $name, array $config) {
        //     return tap($this->createGuard(Auth::createUserProvider($config['provider']), $config), function ($guard) {
        //         app()->refresh('request', $guard, 'setRequest');
        //     });
        // });

        $this->app['config']->get('auth.guards');

        Auth::resolved(function ($auth) {
            $this->extendAuth($auth, 'custom');
        });
    }

    protected function extendAuth($auth, $driver)
    {
        $auth->extend($driver, function ($app, $name, array $config) use ($auth) {
            return tap($this->createGuard($auth, $config), function ($guard) {
                app()->refresh('request', $guard, 'setRequest');
            });
        });

    }

    /**
     * Register the guard.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @param  array  $config
     * @return RequestGuard
     */
    protected function createGuard($auth, $config)
    {
        return new RequestGuard(
            new CustomGuard($auth, config('sanctum.expiration'), $config['provider'], $config['driver']),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }
}
