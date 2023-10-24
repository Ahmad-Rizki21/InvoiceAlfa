<?php

namespace App\Services\Database\Contracts;

interface CacheableModel
{
    public static function getCached();
    public static function flushCached();
}
