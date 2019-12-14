<?php

namespace Awssat\Visits\Traits;

use Illuminate\Support\Collection;

trait Lists
{
    /**
     * Fetch all time trending subjects.
     *
     * @param int $limit
     * @param bool $isLow
     * @return \Illuminate\Support\Collection|array
     */
    public function top($limit = 5, $orderByAsc = false)
    {
        $cacheKey = $this->keys->cache($limit, $orderByAsc);
        $cachedList = $this->cachedList($limit, $cacheKey);
        $visitsIds = $this->getVisitsIds($limit, $this->keys->visits, $orderByAsc);

        if($visitsIds === $cachedList->pluck($this->keys->primary)->toArray() && ! $this->fresh) {
            return $cachedList;
        }

        return $this->freshList($cacheKey, $visitsIds);
    }


    /**
     * Top/low countries
     */
    public function countries($limit = -1, $orderByAsc = false)
    {
        return $this->getSortedList('countries', $limit, $orderByAsc, true);
    }

    /**
     * top/lows refs
     */
    public function refs($limit = -1, $orderByAsc = false)
    {
        return $this->getSortedList('referers', $limit, $orderByAsc, true);
    }

    /**
     * top/lows operating systems
     */
    public function operatingSystems($limit = -1, $orderByAsc = false)
    {
        return $this->getSortedList('OSes', $limit, $orderByAsc, true);
    }

    /**
     * top/lows languages
     */
    public function languages($limit = -1, $orderByAsc = false)
    {
        return $this->getSortedList('languages', $limit, $orderByAsc, true);
    }


    protected function getSortedList($name, $limit, $orderByAsc = false, $withValues = true)
    {
        return $this->connection->valueList($this->keys->visits . "_{$name}:{$this->keys->id}", $limit, $orderByAsc, $withValues);
    }

    /**
     * Fetch lowest subjects.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection|array
     */
    public function low($limit = 5)
    {
        return $this->top($limit, true);
    }


    /**
     * @param $limit
     * @param $visitsKey
     * @param bool $isLow
     * @return mixed
     */
    protected function getVisitsIds($limit, $visitsKey, $orderByAsc = false)
    {
        return array_map(function($item) {
            return is_numeric($item) ? intval($item) : $item;
        }, $this->connection->valueList($visitsKey, $limit - 1, $orderByAsc));
    }

    /**
     * @param $cacheKey
     * @param $visitsIds
     * @return mixed
     */
    protected function freshList($cacheKey, $visitsIds)
    {
        if (count($visitsIds)) {

            $this->connection->delete($cacheKey);

            return ($this->subject)::whereIn($this->keys->primary, $visitsIds)
                ->get()
                ->sortBy(function ($subject) use ($visitsIds) {
                    return array_search($subject->{$this->keys->primary}, $visitsIds);
                })->each(function ($subject) use ($cacheKey) {
                    $this->connection->addToFlatList($cacheKey, serialize($subject));
                });
        }

        return [];
    }

    /**
     * @param $limit
     * @param $cacheKey
     * @return \Illuminate\Support\Collection|array
     */
    protected function cachedList($limit, $cacheKey)
    {
        return Collection::make(
            array_map('unserialize', $this->connection->flatList($cacheKey, $limit))
        );
    }
}
