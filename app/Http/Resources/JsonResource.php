<?php

declare(strict_types=1);

namespace App\Http\Resources;

use DateTimeInterface;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
    protected function numberFormat($num)
    {
        if (is_numeric($num)) {
            return number_format((float) $num, 2);
        }

        return $num;
    }

    protected function formatDateTime($dateTime, $format = null)
    {
        if (empty($dateTime)) {
            return null;
        }

        if (is_string($dateTime)) {
            return $dateTime;
        }

        if ($dateTime instanceof DateTimeInterface && $format) {
            return $dateTime->format($format);
        }

        return (string) $dateTime;
    }

    protected function formatDate($dateTime, $format = null)
    {
        if (empty($dateTime)) {
            return null;
        }

        if (is_string($dateTime)) {
            return $dateTime;
        }

        if ($dateTime instanceof DateTimeInterface && $format) {
            return $dateTime->format($format);
        }

        return $dateTime->format('Y-m-d');
    }

    protected function nullableInt($value)
    {
        return $value === null ? null : (int) $value;
    }

    protected function nullableFloat($value)
    {
        return $value === null ? null : (float) $value;
    }

    protected function nullableBoolean($value)
    {
        return $value === null ? null : (bool) $value;
    }

    protected function nullableString($value)
    {
        return $value === null ? null : (string) $value;
    }
}
