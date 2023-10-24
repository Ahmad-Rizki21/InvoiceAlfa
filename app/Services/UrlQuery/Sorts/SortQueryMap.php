<?php

namespace App\Services\UrlQuery\Sorts;

class SortQueryMap
{
    /**
     * Query column.
     *
     * @var string
     */
    protected $column;

    /**
     * Query direction.
     *
     * @var mixed
     */
    protected $direction;

    /**
     * Query relation.
     *
     * @var string
     */
    protected $relation;

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(string $column)
    {
        list($column, $direction) = $this->parseColumnDirection($column);
        list($column, $relation) = $this->parseColumnRelation(trim($column));

        $this->relation = $relation;
        $this->column = $column;
        $this->direction = $direction;
    }

    protected function parseColumnRelation(string $column)
    {
        if (strpos($column, '.') !== false) {
            $explodedColumn = explode('.', $column);
            $totalExplodedColumn = count($explodedColumn);
            $relation = [];
            $column = '';

            foreach ($explodedColumn as $i => $value) {
                if ($i === $totalExplodedColumn - 1) {
                    $column = $value;
                } else {
                    $relation[] = $value;
                }
            }

            return [$column, implode('.', $relation)];
        }

        return [$column, null];
    }

    public function parseColumnDirection(string $column)
    {
        $direction = 'asc';

        if (substr($column, 0, 1) === '-') {
            $column = substr($column, 1);
            $direction = 'desc';
        }

        return [$column, $direction];
    }

    public function column()
    {
        return $this->column;
    }

    public function direction()
    {
        return $this->direction;
    }

    public function relation(): ?string
    {
        return $this->relation;
    }

    public function hasRelation(): bool
    {
        return !empty($this->relation);
    }

}
