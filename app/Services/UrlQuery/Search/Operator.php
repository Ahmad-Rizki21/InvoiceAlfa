<?php

namespace App\Services\UrlQuery\Search;

class Operator
{
    /**
     * Parsed operator
     *
     * @var string
     */
    protected $operator;

    /**
     * Available operators.
     *
     * @var array
     */
    protected static $availableOperators = [
        'equal' => ['equal', 'equals', '=', 'eq'],
        'notequal' => ['notequal', 'notequals', '!=', '<>', 'neq'],
        'like' => ['like'],
        'notlike' => ['notlike', 'not like', 'nlike', '!like'],
        'in' => ['in'],
        'notin' => ['notin', 'not in', 'nin', '!in'],
        'isnull' => ['isnull', 'is null'],
        'isnotnull' => ['isnotnull', 'is not null', 'notnull', 'nnull', '!null'],
    ];

    /**
     * Instantiate class.
     *
     * @return void
     */
    public function __construct(string $operator)
    {
        $this->operator = strtolower($operator ?: '=');
    }

    /**
     * Get all available operators
     *
     * @return array
     */
    public static function allAvailableOperators()
    {
        $operators = [];

        foreach (static::$availableOperators as $availableOperators) {
            $operators = array_merge($operators, $availableOperators);
        }

        return $operators;
    }

    /**
     * Determine wether current parsed operator is "equal" type.
     *
     * @return bool
     */
    public function isEqual(): bool
    {
        return in_array($this->operator, static::$availableOperators['equal']);
    }

    /**
     * Determine wether current parsed operator is "not equal" type.
     *
     * @return bool
     */
    public function isNotEqual(): bool
    {
        return in_array($this->operator, static::$availableOperators['notequal']);
    }

    /**
     * Determine wether current parsed operator is "like" type.
     *
     * @return bool
     */
    public function isLike(): bool
    {
        return in_array($this->operator, static::$availableOperators['like']);
    }

    /**
     * Determine wether current parsed operator is "not like" type.
     *
     * @return bool
     */
    public function isNotLike(): bool
    {
        return in_array($this->operator, static::$availableOperators['notlike']);
    }

    /**
     * Determine wether current parsed operator is "in" type.
     *
     * @return bool
     */
    public function isIn(): bool
    {
        return in_array($this->operator, static::$availableOperators['in']);
    }

    /**
     * Determine wether current parsed operator is "not in" type.
     *
     * @return bool
     */
    public function isNotIn(): bool
    {
        return in_array($this->operator, static::$availableOperators['notin']);
    }

    /**
     * Determine wether current parsed operator is "is null" type.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return in_array($this->operator, static::$availableOperators['isnull']);
    }

    /**
     * Determine wether current parsed operator is "is not null" type.
     *
     * @return bool
     */
    public function isNotNull(): bool
    {
        return in_array($this->operator, static::$availableOperators['isnotnull']);
    }

    public function value(): string
    {
        $operator = $this->operator;

        switch ($operator) {
            case $this->isEqual():
                return '=';
            case $this->isNotEqual():
                return '!=';
            case $this->isLike():
                return 'like';
            case $this->isNotLike():
                return 'not like';
            case $this->isIn():
                return 'in';
            case $this->isNotIn():
                return 'not in';
            case $this->isNull():
                return 'null';
            case $this->isNotNull():
                return 'not null';
            default:
                return '=';
        }

        return $operator;
    }

    public function operator(): string
    {
        return $this->value();
    }

    public function __toString()
    {
        return $this->operator();
    }
}
