<?php

namespace App\Services\Database\Eloquent;

use App\Services\UrlQuery\ModelFilter;
use App\Services\UrlQuery\UrlQuery;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Str;

abstract class Model extends IlluminateModel
{
    /**
     * {@inheritdoc}
     */
    public static $snakeAttributes = true;

    /**
     * {@inheritdoc}
     */
    const CREATED_AT = 'created_at';

    /**
     * {@inheritdoc}
     */
    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    // /**
    //  * Create a new Eloquent query builder for the model.
    //  *
    //  * @param \Illuminate\Database\Query\Builder $query
    //  *
    //  * @return \oyvoy\Support\CrossDatabase\Eloquent\Builder|static
    //  */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function withRelationConnection($connection)
    {
        $instance = clone $this;

        return $instance->setConnection($connection);
    }

    public static function getDatabaseNameFromConfig($config)
    {
        return config('database.connections.'.$config.'.database');
    }

    public function getConnectionConfig(): array
    {
        $connectionName = $this->getConnectionName();

        if (! $connectionName) {
            return  $this->getConnection()->getConfig();
        }

        return config('database.connections.'.$connectionName, []);
    }

    public function getAllColumns()
    {
        $fillable = $this->getFillable();

        $fillable[] = $this->getKeyName();

        if ($this->usesTimestamps()) {
            $fillable[] = $this->getCreatedAtColumn();
            $fillable[] = $this->getUpdatedAtColumn();

            if (method_exists($this, 'getDeletedAtColumn')) {
                $fillable[] = $this->getDeletedAtColumn();
            }
        }

        return array_unique($fillable);
    }

    public function getAllQualifiedColumns()
    {
        $fillable = [];

        foreach ($this->getFillable() as $value) {
            $fillable[] = $this->qualifyColumn($value);
        }

        $fillable[] = $this->getQualifiedKeyName();

        if ($this->usesTimestamps()) {
            $fillable[] = $this->getQualifiedCreatedAtColumn();
            $fillable[] = $this->getQualifiedUpdatedAtColumn();

            if (method_exists($this, 'getQualifiedDeletedAtColumn')) {
                $fillable[] = $this->getQualifiedDeletedAtColumn();
            }
        }

        return array_unique($fillable);
    }

    public function isColumnExists(string $column)
    {
        return in_array($column, $this->getAllColumns());
    }

    public function getTable()
    {
        return $this->getQualifiedTable();
    }

    public function getRawTable()
    {
        return $this->table ?? Str::camel(Str::pluralStudly(class_basename($this)));
    }

    public function getCreatedAt()
    {
        return $this->{$this->getCreatedAtColumn()};
    }

    public function getUpdatedAt()
    {
        return $this->{$this->getCreatedAtColumn()};
    }

    public function getDeletedAt()
    {
        return $this->{$this->getDeletedAtColumn()};
    }

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getDeletedAtColumn()
    {
        $column = defined(static::class . '::DELETED_AT') ? static::DELETED_AT : 'deleted_at';

        if (static::$snakeAttributes) {
            return $column;
        }

        return Str::camel($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getForeignKey()
    {
        if (! static::$snakeAttributes) {
            return Str::camel(class_basename($this)) . ' ' . ucfirst($this->getKeyName());
        }

        return parent::getForeignKey();
    }

    /**
     * {@inheritdoc}
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey)) {
            if (static::$snakeAttributes) {
                $foreignKey = Str::snake($relation) . '_' . $instance->getKeyName();
            } else {
                $foreignKey = Str::camel($relation) . Str::studly($instance->getKeyName());
            }
        }

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newBelongsTo(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }

    /**
     * {@inheritdoc}
     */
    public function morphTo($name = null, $type = null, $id = null, $ownerKey = null)
    {
        $name = $name ?: $this->guessBelongsToRelation();

        [$type, $id] = $this->getMorphs(
            static::$snakeAttributes ? Str::snake($name) : Str::camel($name), $type, $id
        );

        return is_null($class = $this->getAttributeFromArray($type)) || $class === ''
                    ? $this->morphEagerTo($name, $type, $id, $ownerKey)
                    : $this->morphInstanceTo($class, $name, $type, $id, $ownerKey);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMorphs($name, $type, $id)
    {
        if (static::$snakeAttributes) {
            return parent::getMorphs($name, $type, $id);
        }

        return [$type ?: $name.'Type', $id ?: $name.'Id'];
    }

    /**
     * {@inheritdoc}
     */
    public function joiningTable($related, $instance = null)
    {
        $segments = [
            $instance ? $instance->joiningTableSegment()
                : (static::$snakeAttributes ? Str::snake(class_basename($related)) : Str::camel(class_basename($related))),
            $this->joiningTableSegment(),
        ];

        sort($segments);

        return strtolower(implode('', $segments));
    }

    /**
     * {@inheritdoc}
     */
    public function joiningTableSegment()
    {
        if (static::$snakeAttributes) {
            return parent::joiningTableSegment();
        }

        return Str::camel(class_basename($this));
    }

    public function getQualifiedTable()
    {
        $config = $this->getConnectionConfig();
        $database = $config['database'] ?? null;
        $schema = $config['schema'] ?? null;
        $table = $this->getRawTable();

        if (strpos($table, '.') !== false || stripos($table, 'laravel_reserved') !== false) {
            return $table;
        }

        $definitions = [$database, $schema, $table];
        $definitions = array_filter($definitions);

        return implode('.', $definitions);
    }

    public function qualifyColumn($column)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getQualifiedTable() . '.' . $column;
    }


    public function scopeFilter(IlluminateBuilder $query, ModelFilter $modelFilter = null, UrlQuery $urlQuery = null)
    {
        $model = $this;
        $urlQuery = $urlQuery ?: app(UrlQuery::class);
        $searches = $urlQuery->searches();
        $trashed = $urlQuery->trashed();
        $sorts = $urlQuery->sorts();
        $includes = $urlQuery->includes();
        $excludes = $urlQuery->excludes();

        foreach ($searches->values() as $search) {
            $operator = $search->operator();

            if ($search->hasRelation()) {
                $query->whereHas($search->relation(), function ($query) use ($search, $operator) {
                    $value = $search->value();
                    $model = $query->getModel();
                    $column = $search->column();

                    if ($operator->isLike()) {
                        if ($model->getKeyName() !== $search->column()) {
                            $value = '%' . $value . '%';
                        }
                    }

                    $column = $model->qualifyColumn($column);

                    if ($operator->isIn() || $operator->isNotIn()) {
                        $query->whereIn($column, $value,
                            'and',
                            $operator->isNotIn()
                        );
                    } else if ($operator->isNull() || $operator->isNotNull()) {
                        $query->whereNull($column, 'and', $operator->isNotNull());
                    } else {
                        $query->where($column, $operator->value(), $value);
                    }
                });
            } else if ($modelFilter && ($matchMethod = $modelFilter->getMatchMethod($search))) {
                $modelFilter->{$matchMethod}($search->value(), $search->operator());
            } else if ($model->isColumnExists($search->column())) {
                $value = $search->value();
                $column = $search->column();

                if ($operator->isLike()) {
                    if ($model->getKeyName() === $search->column()) {
                        $value .= '%';
                    } else {
                        $value = '%' . $value . '%';
                    }
                }

                $column = $model->qualifyColumn($column);

                if ($operator->isIn() || $operator->isNotIn()) {
                    $query->whereIn($column, $value, 'and', $operator->isNotIn());
                } else if ($operator->isNull() || $operator->isNotNull()) {
                    $query->whereNull($column, 'and', $operator->isNotNull());
                } else {
                    $query->where($column, $operator->value(), $value);
                }
            }
        }

        if ($sorts->hasValues()) {
            foreach ($sorts->values() as $sort) {
                if ($sort->hasRelation()) {
                    $query->without($sort->relation())->with([$sort->relation() => function ($query) use ($sort) {
                        $query->orderBy($sort->column(), $sort->direction());
                    }]);
                } else if ($model->isColumnExists($sort->column())) {
                    $query->orderBy($sort->column(), $sort->direction());
                }
            }
        } else if ($modelFilter) {
            $modelFilter->defaultSort();
        }

        if ($includes->hasValues()) {
            $query->with($includes->values());
        }

        if ($excludes->hasValues()) {
            $query->without($excludes->values());
        }

        if ($trashed && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }
    }
}
