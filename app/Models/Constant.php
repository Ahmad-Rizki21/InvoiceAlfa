<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Str;
use ReflectionClass;

class Constant
{
    protected static $data;

    protected static $classes = [
        \App\Models\Role::class,
        \App\Models\UserAccessToken::class,
        \App\Enums\SettingKey::class,
    ];

    public static function all()
    {
        if (static::$data) {
            return static::$data;
        }

        $data = [];

        foreach (static::$classes as $key => $class) {
            $data[is_numeric($key) ? Str::snake(class_basename($class)): $key] = static::generateConstants($class);
        }

        return (static::$data = $data);
    }

    protected static function generateConstants($class)
    {
        $class = new ReflectionClass($class);

        $constants = $class->getConstants();
        $excludes = ['CREATED_AT', 'UPDATED_AT'];

        if ($constants) {
            foreach ($constants as $key => $value) {
                if (!in_array($key, $excludes)) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
