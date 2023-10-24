<?php

declare(strict_types=1);

namespace App\Services\DataTransferObject;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

class Dto implements Arrayable
{
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }

        }
    }

    public static function fromRequest(Request $request): static
    {
        return new static($request->all());
    }

    public static function fromArray(array $array): static
    {
        return new static($array);
    }

    public function all(): array
    {
        $data = [];

        $class = new ReflectionClass(static::class);

        $properties = $class->getProperties(ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $data[$property->getName()] = $property->getValue($this);
        }

        return $data;
    }

    public function toArray()
    {
        return $this->all();
    }

    // public function setAttribute(string $key, $value)
    // {
    //     $this->{$key} = $value;
    // }

    // public function getAttribute(string $key, mixed $default = null)
    // {
    //     return $this->{$key} ?: $default;
    // }

    // public function __call($name, $arguments)
    // {
    //     $accessor = substr($name, 0, 3);
    //     $isAccessor = in_array($accessor, ['get', 'set']);

    //     if ($isAccessor) {
    //         $property = substr($name, 3);
    //         $cameledProperty = Str::camel($property);

    //         if (property_exists($this, $cameledProperty)) {
    //             return $this->{$accessor . 'Attribute'}($cameledProperty, ...$arguments);
    //         }

    //         $snakedProperty = Str::snake($property);

    //         if (property_exists($this, $snakedProperty)) {
    //             return $this->{$accessor . 'Attribute'}($snakedProperty, ...$arguments);
    //         }

    //         throw new InvalidArgumentException("Property $cameledProperty does not exists");
    //     }

    //     throw new InvalidArgumentException("Method $name does not exists");
    // }
}
