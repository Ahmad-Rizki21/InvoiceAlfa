<?php

namespace App\Services\UrlQuery\Sorts;

class SortBuilder
{
    /**
     * Original values before mutated.
     *
     * @var string
     */
    protected $original = '';

    /**
     * Search values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Query parameter separator.
     *
     * @var string
     */
    protected $querySeparator = '|';

    /**
     * Instantiate class
     *
     * @return void
     */
    public function __construct(string $values = '')
    {
        $values = trim($values);

        if (! empty($values)) {
            $values = trim($values, $this->querySeparator);

            $this->original = $values;

            $this->values = explode($this->querySeparator, $values);

            $this->buildValues();
        }
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->values);
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

    /**
     * Determine whether has values.
     *
     * @return bool
     */
    public function hasValues(): bool
    {
        return count($this->values) > 0;
    }

    /**
     * Get the original values.
     *
     * @return string
     */
    public function original(): string
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
        $values = [];

        foreach ($this->values as $value) {
            $value = new SortQueryMap($value);

            $values[$value->column()] = $value;
        }

        $this->values = $values;
    }
}
