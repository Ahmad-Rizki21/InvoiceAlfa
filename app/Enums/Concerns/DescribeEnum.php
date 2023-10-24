<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Illuminate\Support\Str;
use ReflectionClass;

trait DescribeEnum
{
    public static function values()
    {
        $reflectionClass = new ReflectionClass(self::class);

        $result = [];

        foreach ($reflectionClass->getConstants() as $constant) {
            $result[] = $constant->value;
        }

        return $result;
    }

    public static function labels()
    {
        $reflectionClass = new ReflectionClass(self::class);

        $result = [];

        foreach ($reflectionClass->getConstants() as $key => $constant) {
            $result[] = [
                'value' => $constant->value,
                'key' => $key,
                'description' => $constant->description(),
            ];
        }

        return $result;
    }

    public function key(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return __(Str::headline($this->name));
    }

    public static function labelFrom($value): array
    {
        $instance = self::from($value);

        return [
            'value' => $value,
            'key' => $instance->name,
            'description' => $instance->description(),
        ];
    }
}
