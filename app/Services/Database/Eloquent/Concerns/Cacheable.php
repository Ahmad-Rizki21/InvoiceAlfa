<?php

namespace App\Services\Database\Eloquent\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Enumerable;

trait Cacheable
{
    protected static $cacheableStore = 'redis';

    protected static function bootCacheable(): void
    {
        static::saving(function () {
            static::flushCached();
        });

        static::deleted(function () {
            static::flushCached();
        });
    }

    public static function getCached(): Enumerable
    {
        return Cache::store(static::$cacheableStore)->rememberForever(static::class, function () {
            return static::get();
        });
    }
    public static function flushCached(): void
    {
        Cache::store(static::$cacheableStore)->forget(static::class);
    }
}
