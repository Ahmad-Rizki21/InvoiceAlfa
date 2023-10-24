<?php

namespace App\Services\UrlQuery\Search;

use App\Services\UrlQuery\Contracts\SearchMap;

class SearchBuilder
{
    /**
     * Original values before mutated.
     *
     * @var array
     */
    protected $original = [];

    /**
     * Search values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * The parent relation.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(array $values)
    {
        $this->original = $values;

        $this->values = $values;

        $this->buildValues();
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
        return ($this->values[$key] ?? null) ?: ($default instanceof SearchQueryMap ?
            $default :
            new SearchQueryMap($key, null)
        );
    }

    /**
     * Get relations.
     *
     * @return array
     */
    public function relations(): array
    {
        return $this->relations;
    }

    public function hasRelation($key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    public function getRelation($key, $default = null)
    {
        return ($this->relations[$key] ?? null) ?: ($default instanceof SearchRelationCollection ?
            $default :
            new SearchRelationCollection($key, null)
        );
    }

    /**
     * Get the original values.
     *
     * @return array
     */
    public function original(): array
    {
        return $this->original;
    }

    /**
     * Convert values to guessed types.
     *
     * @return void
     */
    protected function buildValues()
    {
        $values = $this->values;

        foreach ($values as $key => $value) {
            if (strpos($key, '>') !== false) {
                $relation = explode('>', $key)[0];

                if (substr($relation, 0, 1) === ':') {
                    $relation = substr($relation, 1);
                }

                if (! isset($this->relations[$relation])) {
                    $this->relations[$relation] = new SearchRelationCollection($relation);
                }

                $this->relations[$relation]->add(new SearchQueryMap($key, $value ?? ''));

                unset($values[$key]);
            } else if (! $value || is_string($value)) {
                $values[$key] = new SearchQueryMap($key, $value ?? '');
            } else {
                unset($values[$key]);
            }
        }

        $this->values = $values;
    }
}
