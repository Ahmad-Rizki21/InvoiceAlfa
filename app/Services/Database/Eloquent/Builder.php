<?php

namespace App\Services\Database\Eloquent;

use App\Services\Database\Contracts\CacheableModel;
use App\Services\Database\Eloquent\Concerns\QueriesRelationships as CrossDatabaseQueriesRelationships;
use Illuminate\Database\Eloquent\Builder as IlluminateEloquentBuilder;
use Illuminate\Database\Eloquent\Concerns\QueriesRelationships as IlluminateEloquentQueriesRelationships;
use Closure;

class Builder extends IlluminateEloquentBuilder
{
    use IlluminateEloquentQueriesRelationships, CrossDatabaseQueriesRelationships {
        CrossDatabaseQueriesRelationships::addHasWhere insteadof IlluminateEloquentQueriesRelationships;
        CrossDatabaseQueriesRelationships::withAggregate insteadof IlluminateEloquentQueriesRelationships;
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array  $models
     * @param  string  $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        if ($relation->getRelated() instanceof CacheableModel) {
            $relationClass = get_class($relation->getRelated());

            return $relation->match(
                $relation->initRelation($models, $name),
                $relationClass::getCached(),
                $name
            );
        }

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(),
            $name
        );
    }

}
