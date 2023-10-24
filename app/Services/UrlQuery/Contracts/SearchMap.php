<?php

namespace App\Services\UrlQuery\Contracts;

use App\Services\UrlQuery\Search\Operator;

interface SearchMap
{
    public function column(): string;

    public function operator(): Operator;

    public function value();

    public function filled(): bool;
}
