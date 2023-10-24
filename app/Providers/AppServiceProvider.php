<?php

namespace App\Providers;

use App\Models\UserAccessToken;
use App\Services\Minifier\BladeCompiler;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::$personalAccessTokenModel = UserAccessToken::class;

        if ($this->app->runningInConsole()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        Passport::$registersRoutes = false;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

        SchemaBuilder::defaultStringLength(191);

        $this->app->singleton('blade.compiler', function ($app) {
            return new BladeCompiler($app['files'], $app['config']['view.compiled']);
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Relation::enforceMorphMap([
        //     Administrator::morphAlias() => Administrator::class,
        //     Candidate::morphAlias() => Candidate::class,
        //     Supporter::morphAlias() => Supporter::class,
        //     Volunteer::morphAlias() => Volunteer::class,
        // ]);
    }
}
