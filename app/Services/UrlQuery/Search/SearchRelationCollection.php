<?php

namespace App\Services\UrlQuery\Search;

use App\Services\UrlQuery\Contracts\SearchMap;

class SearchRelationCollection
{
    /**
     * The relation name to search for.
     *
     * @var string
     */
    protected string $relation;

    /**
     * Search values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(string $relation)
    {
        $this->relation = $relation;
    }

    /**
     * Get converted values.
     *
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get($key, $default = null)
    {
        return ($this->values[$key] ?? null) ?: (
            $default instanceof SearchMap ?
                $default :
                new SearchQueryMap($key, null)
        );
    }

    public function add(SearchQueryMap|string $column, ?string $value = null)
    {
        if ($column instanceof SearchQueryMap) {
            $this->values[$column->column()] = $column;
        } else {
            $this->values[$column] = new SearchQueryMap($column, $value);
        }
    }
}
