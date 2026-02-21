<?php

namespace Awssat\Visits\Relations;

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
}
