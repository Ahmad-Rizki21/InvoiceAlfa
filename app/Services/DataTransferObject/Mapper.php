<?php

declare(strict_types=1);

namespace App\Services\DataTransferObject;

use App\Services\Database\Eloquent\Model;
use Illuminate\Support\Enumerable;

class Mapper extends Dto
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

    public static function fromModel(Model $model): static
    {
        return new static($model->toArray());
    }

    public static function collectionFromArray(array $array): MapperCollection
    {
        $result = new MapperCollection();

        $returnMessage = '';

        foreach ($array as $key => $value) {
            $result[$key] = static::fromArray($value);

            if (empty($returnMessage)) {
                $returnMessage = $result[$key]->getReturnMessage();
            }
        }

        $result->setReturnMessage($returnMessage);

        return $result;
    }

    public static function collectionFromModels(Enumerable|Model $models): MapperCollection
    {
        if ($models instanceof Model) {
            $models = collect($models);
        }

        $result = new MapperCollection();

        $returnMessage = '';

        foreach ($models as $key => $value) {
            $result[$key] = static::fromModel($value);

            if (empty($returnMessage)) {
                $returnMessage = $result[$key]->getReturnMessage();
            }
        }

        $result->setReturnMessage($returnMessage);

        return $result;
    }
}
