<?php

namespace App\Services\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait ConcatsFullName
{
    /**
     * Get the full name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $attributes = $this->attributes;
                $firstName = $attributes['first_name'] ?? null;
                $middleName = $attributes['middle_name'] ?? null;
                $lastName = $attributes['last_name'] ?? null;
                $nameSuffix = $attributes['name_suffix'] ?? null;

                return implode(' ', array_filter([
                    $firstName, $middleName, $lastName, $nameSuffix
                ]));
            }
        );
    }
}
