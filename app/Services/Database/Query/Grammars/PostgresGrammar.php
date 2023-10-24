<?php

namespace App\Services\Database\Query\Grammars;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Illuminate\Support\Facades\Log;

class PostgresGrammar extends IlluminatePostgresGrammar
{
    /**
     * Compile the "from" portion of the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $table
     *
     * @return string
     */
    protected function compileFrom(Builder $query, $table)
    {
        if ($table instanceof Expression) {
            return 'from ' . $table->getValue($this);
        }

        // Check for cross database query to attach database name
        if (strpos($table, '<-->') !== false) {
            list($prefix, $table, $database) = explode('<-->', $table);
            $wrappedTable = $this->wrapTable($table, true);
            $wrappedTablePrefixed = $this->wrap($prefix.$table, true);
            if ($wrappedTable != $wrappedTablePrefixed) {
                return 'from '.$this->wrap($database).'.'.$wrappedTablePrefixed.' as '.$wrappedTable;
            }

            return 'from '.$this->wrap($database).'.'.$wrappedTablePrefixed;
        }

        if (stripos($table, ' ') === false) {
            $connection = $query->getConnection();
            $database = $connection->getConfig('database') ?? null;
            $schema = $connection->getConfig('schema') ?? null;

            $tableDefinition = explode('.', $table);
            $totalTableDefinition = count($tableDefinition);

            if ($totalTableDefinition === 1) {
                $definitions = [$database, $schema, $table];
                $table = implode('.', array_filter($definitions));
            } else if ($totalTableDefinition === 2) {
                Log::info('TOTAL 2 TABLE DEFINITIONS FOUND IN CONNECTION', [
                    'table_definition' => $tableDefinition,
                    'database' => $database,
                    'schema' => $schema,
                ]);
            }
        }

        return 'from '.$this->wrapTable($table);
    }
}
