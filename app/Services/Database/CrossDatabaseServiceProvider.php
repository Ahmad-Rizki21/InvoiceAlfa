<?php

namespace App\Services\Database;

use App\Services\Database\Connectors\ConnectionFactory;
use App\Services\Database\Schema\Blueprint;
use App\Services\UrlQuery\Search\SearchQueryMap;
use App\Services\UrlQuery\Search\SearchRelationCollection;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\ServiceProvider;

class CrossDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);

        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // $this->registerBlueprintResolver();

        $this->registerSearchQueryMacro();
    }

    protected function registerBlueprintResolver()
    {
        $this->app->alias(Blueprint::class, IlluminateBlueprint::class);
    }

    protected function registerSearchQueryMacro()
    {
        EloquentBuilder::macro('searchQuery', function (mixed $columns, ?SearchQueryMap $map = null) {
            if ($columns instanceof Closure) {
                return $this->where(fn ($query) => $columns($query));
            }

            if ($map instanceof SearchQueryMap && $map->filled()) {
                $operator = $map->operator();

                if ($operator->isNull() || $operator->isNotNull()) {
                    $this->whereNull(columns: $columns, not: $operator->isNotNull());
                } else if ($operator->isIn() || $operator->isNotIn()) {
                    $this->whereIn(
                        column: $columns,
                        values: $map->value(),
                        not: $operator->isNotIn()
                    );
                } else {
                    $model = $this->getModel();
                    $value = $map->value();

                    if ($operator->isLike() || $operator->isNotLike()) {
                        if (is_numeric($value) || $map->column() === $model->getKeyName()) {
                            $value = $value . '%';
                        } else {
                            $value = '%' . $value . '%';
                        }
                    }

                    $this->where($columns, $operator->value(), $value);
                }
            }

            return $this;
        });

        EloquentBuilder::macro('searchRelation', function (SearchRelationCollection|Closure $collection) {
            if ($collection instanceof Closure) {
                return $this->where(fn ($query) => $collection($query));
            }

            if ($collection instanceof SearchRelationCollection) {
                foreach ($collection->values() as $map) {
                    if ($map->hasRelation()) {
                        $relation = $map->relation();

                        $this->whereHas($relation, function ($query) use ($map) {
                            $query->searchQuery($map->column(), $map);
                            // $column = $map->column();
                            // $query = $this->getModel();

                            // if ($operator->isNull() || $operator->isNotNull()) {
                            //     $query->whereNull(columns: $column, not: $operator->isNotNull());
                            // } else if ($operator->isIn() || $operator->isNotIn()) {
                            //     $query->whereIn(
                            //         column: $column,
                            //         values: $map->value(),
                            //         not: $operator->isNotIn()
                            //     );
                            // } else {
                            //     $value = $map->value();

                            //     if ($operator->isLike() || $operator->isNotLike()) {
                            //         if (is_numeric($value) || $map->column() === $model->getKeyName()) {
                            //             $value = $value . '%';
                            //         } else {
                            //             $value = '%' . $value . '%';
                            //         }
                            //     }

                            //     $query->where($column, $operator->value(), $value);
                            // }
                        });

                        if ($map->includesEager()) {
                            $this->without($relation)->with([$relation => function ($query) use ($map) {
                                $query->searchQuery($map->column(), $map);
                            }]);
                        }
                    }
                }
            }

            return $this;
        });
    }
}
