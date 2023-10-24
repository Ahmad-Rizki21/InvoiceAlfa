<?php

namespace App\Services\UrlQuery;

use App\Services\UrlQuery\Search\SearchQueryMap;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ModelFilter
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function getMatchMethod(SearchQueryMap $search): ?string
    {
        $column = $search->column();
        $cameledColumn = Str::camel($column);

        $guessableMethods = [$column, $cameledColumn, 'search' . ucfirst($cameledColumn)];

        foreach ($guessableMethods as $method) {
            if (method_exists($this, $method)) {
                return $method;
                return $this->{$method}($search->value(), $search->operator());
            }
        }
    }

    public function defaultSort()
    {
        //
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->query, $method)) {
            return $this->query->{$method}(...$parameters);
        }

        throw new InvalidArgumentException("Method $method does not exists");
    }
}
