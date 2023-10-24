<?php

namespace App\Services\UrlQuery\Search;

use Illuminate\Support\Str;

class SearchQueryMap
{
    /**
     * Query column.
     *
     * @var string
     */
    protected $column;

    /**
     * Query relation.
     *
     * @var string
     */
    protected $relation;

    /**
     * Query operator.
     *
     * @var \App\Services\UrlQuery\Search\Operator
     */
    protected $operator;

    /**
     * Query value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Determine if should filter eager loaded.
     *
     * @var bool
     */
    protected bool $includesEager = false;

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(string $column, ?string $value)
    {
        $value = (string) $value;

        list($column, $relation) = $this->parseColumnRelation(trim($column));

        $this->column = $column;
        $this->relation = $relation;

        list($operator, $value) = $this->parseOperatorValue(trim($value));

        $this->operator = $operator;

        $value = $this->parseArrayValue($value);
        $value = $this->coerceValueTypes($value);

        $this->value = $value;
    }

    public function column(): string
    {
        return $this->column;
    }

    public function operator(): Operator
    {
        return $this->operator;
    }

    public function value()
    {
        return $this->value;
    }

    public function filled(): bool
    {
        return ! empty($this->value);
    }

    public function relation(): ?string
    {
        return $this->relation;
    }

    public function hasRelation(): bool
    {
        return ! empty($this->relation);
    }

    public function includesEager(): bool
    {
        return $this->includesEager;
    }

    protected function parseColumnRelation(string $column)
    {
        if (strpos($column, '>') !== false) {
            $explodedColumn = explode('>', $column);
            $totalExplodedColumn = count($explodedColumn);
            $relation = [];
            $column = '';
            $includesEager = false;

            foreach ($explodedColumn as $i => $value) {
                if (substr($value, 0, 1) === ':') {
                    $includesEager = true;
                    $value = substr($value, 1);
                }

                if ($i === $totalExplodedColumn - 1) {
                    $column = $value;
                } else {
                    $relation[] = Str::camel($value);
                }
            }

            $this->includesEager = $includesEager;

            return [$column, implode('.', $relation)];
        }

        return [$column, null];
    }

    protected function parseOperatorValue(string $value)
    {
        foreach (Operator::allAvailableOperators() as $availableOperator) {
            if (stripos($value, $availableOperator . ':') === 0) {
                $explodedValue = explode(':', $value, 2);

                return [new Operator($explodedValue[0]), $explodedValue[1]];
            }
        }

        return [new Operator('='), $value];
    }

    protected function parseArrayValue(string $value)
    {
        if (strpos($value, '|') !== false) {
            $explodedValue = explode('|', $value);

            return $explodedValue;
        }

        if (($this->operator->isIn() || $this->operator->isNotIn())) {
            return [$value];
        }

        return $value;
    }

    protected function coerceValueTypes($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->coerceValueType($v);
            }
        } else {
            $value = $this->coerceValueType($value);
        }

        return $value;
    }

    protected function coerceValueType($value)
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return $value;
            }

            return (int) $value;
        }

        return $value;
    }
}
