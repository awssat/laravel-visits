<?php

namespace Awssat\Visits\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class VisitsHasOne extends HasOne
{
    /**
     * Get all of the primary keys for an array of models.
     *
     * @param  array  $models
     * @param  string|null  $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return collect($models)->map(function ($value) use ($key) {
            return (string) ($key ? $value->getAttribute($key) : $value->getKey());
        })->values()->unique(null, true)->sort()->all();
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @return string
     */
    protected function whereInMethod(Model $model, $key)
    {
        return 'whereIn';
    }

    /**
     * Add the constraints for a relationship count query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($query->getQuery()->from == $parentQuery->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        if ($query->getConnection()->getDriverName() === 'pgsql') {
            $grammar = $query->getQuery()->getGrammar();

            return $query->select($columns)->whereRaw(
                "{$grammar->wrap($this->getExistenceCompareKey())} = CAST({$grammar->wrap($this->getQualifiedParentKeyName())} AS VARCHAR)"
            );
        }

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Add the constraints for a relationship count query on the same table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        $grammar = $query->getQuery()->getGrammar();

        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return $query->select($columns)->whereRaw(
                "{$grammar->wrap($hash.'.'.$this->getForeignKeyName())} = CAST({$grammar->wrap($this->getQualifiedParentKeyName())} AS VARCHAR)"
            );
        }

        return $query->select($columns)->whereColumn(
            $hash.'.'.$this->getForeignKeyName(), '=', $this->getQualifiedParentKeyName()
        );
    }
}
