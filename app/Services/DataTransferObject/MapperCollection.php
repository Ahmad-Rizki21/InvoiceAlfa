<?php

declare(strict_types=1);

namespace App\Services\DataTransferObject;

use Illuminate\Support\Collection;

class MapperCollection extends Collection
{
    protected string $returnMessage = '';

    public function getReturnMessage(): string
    {
        return $this->returnMessage;
    }

    public function setReturnMessage(string $returnMessage): void
    {
        $this->returnMessage = $returnMessage;
    }
}
