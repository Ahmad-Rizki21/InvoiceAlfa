<?php

namespace App\Services\Database\Schema;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{

    /**
     * Indicate that the timestamp columns should be dropped.
     *
     * @return void
     */
    public function dropTimestamps()
    {
        $this->dropColumn('createdAt', 'updatedAt');
    }

    /**
     * Indicate that the soft delete column should be dropped.
     *
     * @param  string  $column
     * @return void
     */
    public function dropSoftDeletes($column = 'deleted_at')
    {
        if ($column === 'deleted_at') {
            $column = 'deletedAt';
        }

        $this->dropColumn($column);
    }

    /**
     * Indicate that the remember token column should be dropped.
     *
     * @return void
     */
    public function dropRememberToken()
    {
        $this->dropColumn('rememberToken');
    }

    /**
     * Indicate that the polymorphic columns should be dropped.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function dropMorphs($name, $indexName = null)
    {
        $this->dropIndex($indexName ?: $this->createIndexName('index', ["{$name}Type", "{$name}Id"]));

        $this->dropColumn("{$name}Type", "{$name}Id");
    }

    /**
     * Create a new date-time column on the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function dateTime($column, $precision = 0)
    {
        return $this->addColumn('dateTimeTz', $column, compact('precision'));
    }

    /**
     * Create a new time column on the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function time($column, $precision = 0)
    {
        return $this->addColumn('timeTz', $column, compact('precision'));
    }

    /**
     * Create a new timestamp column on the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function timestamp($column, $precision = 0)
    {
        return $this->addColumn('timestampTz', $column, compact('precision'));
    }

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @param  int|null  $precision
     * @return void
     */
    public function timestamps($precision = 0)
    {
        $this->timestamp('createdAt', $precision)->nullable();

        $this->timestamp('updatedAt', $precision)->nullable();
    }

    /**
     * Add creation and update timestampTz columns to the table.
     *
     * @param  int|null  $precision
     * @return void
     */
    public function timestampsTz($precision = 0)
    {
        $this->timestampTz('createdAt', $precision)->nullable();

        $this->timestampTz('updatedAt', $precision)->nullable();
    }

    /**
     * Add creation and update datetime columns to the table.
     *
     * @param  int|null  $precision
     * @return void
     */
    public function datetimes($precision = 0)
    {
        $this->datetime('createdAt', $precision)->nullable();

        $this->datetime('updatedAt', $precision)->nullable();
    }

    /**
     * Add a "deleted at" timestamp for the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletes($column = 'deleted_at', $precision = 0)
    {
        if ($column === 'deleted_at') {
            $column = 'deletedAt';
        }

        return $this->timestamp($column, $precision)->nullable();
    }

    /**
     * Add a "deleted at" timestampTz for the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletesTz($column = 'deleted_at', $precision = 0)
    {
        if ($column === 'deleted_at') {
            $column = 'deletedAt';
        }

        return $this->timestampTz($column, $precision)->nullable();
    }

    /**
     * Add a "deleted at" datetime column to the table.
     *
     * @param  string  $column
     * @param  int|null  $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletesDatetime($column = 'deleted_at', $precision = 0)
    {
        if ($column === 'deleted_at') {
            $column = 'deletedAt';
        }

        return $this->datetime($column, $precision)->nullable();
    }

    /**
     * Create a new IP address column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function ipAddress($column = 'ip_address')
    {
        if ($column === 'ip_address') {
            $column = 'ipAddress';
        }

        return $this->addColumn('ipAddress', $column);
    }

    /**
     * Create a new MAC address column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function macAddress($column = 'mac_address')
    {
        if ($column === 'mac_address') {
            $column = 'macAddress';
        }

        return $this->addColumn('macAddress', $column);
    }

    /**
     * Add the proper columns for a polymorphic table using numeric IDs (incremental).
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function numericMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type");

        $this->unsignedBigInteger("{$name}Id");

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using numeric IDs (incremental).
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function nullableNumericMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type")->nullable();

        $this->unsignedBigInteger("{$name}Id")->nullable();

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Add the proper columns for a polymorphic table using UUIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function uuidMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type");

        $this->uuid("{$name}Id");

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using UUIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function nullableUuidMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type")->nullable();

        $this->uuid("{$name}Id")->nullable();

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Add the proper columns for a polymorphic table using ULIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function ulidMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type");

        $this->ulid("{$name}Id");

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Add nullable columns for a polymorphic table using ULIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     */
    public function nullableUlidMorphs($name, $indexName = null)
    {
        $this->string("{$name}Type")->nullable();

        $this->ulid("{$name}Id")->nullable();

        $this->index(["{$name}Type", "{$name}Id"], $indexName);
    }

    /**
     * Adds the `rememberToken` column to the table.
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function rememberToken()
    {
        return $this->string('rememberToken', 100)->nullable();
    }
}

