<?php

declare(strict_types=1);

namespace App\Services\Http;

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
}
