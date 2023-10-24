<?php

namespace App\Services\Service;

class QuantityType
{
    public const TYPE_TOTAL_DISTRIBUTION_CENTER = 1;
    public const TYPE_TOTAL_STORE = 2;
    public const TYPE_FREE_NUMBER = 3;
    public const ALL_TYPES = [
        self::TYPE_TOTAL_DISTRIBUTION_CENTER,
        self::TYPE_TOTAL_STORE,
        self::TYPE_FREE_NUMBER,
    ];
}
