<?php

namespace if4lcon\Bareq;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class Visits
{
    protected $ipSeconds;
    protected $subject;
    protected $fresh = false;
    protected $periods;
    protected $keys;

    /**
     * Visits constructor.
     * @param $subject
     * @param Keys|null $keys
     */
    public function __construct($subject = null, Keys $keys = null)
    {
        $config          = config('bareq');
        $this->periods   = $config['periods'];
        $this->ipSeconds = $config['remember_ip'];
        $this->fresh     = $config['always_fresh'];
        $this->subject   = $subject;
        $this->keys      = ($keys) ? $keys : new Keys($subject);
    }

    /**
     * Return fresh cache from database
     * @return $this
     */
    public function fresh()
    {
        $this->fresh = true;

        return $this;
    }

    /**
     * set x seconds for ip expiration
     *
     * @param $seconds
     * @return $this
     */
    public function seconds($seconds)
    {
        $this->ipSeconds = $seconds;

        return $this;
    }

    /**
     * Change period
     *
     * @param $period
     * @return $this
     */
    public function period($period)
    {
        if (in_array($period, array_keys($this->periods))) {
            $this->keys->visits = $this->keys->period($period);
        }

        return $this;
    }

    /**
     * Reset methods
     *
     * @param $method
     * @param string $args
     * @return Reset
     */
    public function reset($method = 'visits', $args = '')
    {
        return new Reset($this, $this->keys, $method, $args);
    }

    /**
     * Fetch all time trending subjects.
     *
     * @param int $limit
     * @param bool $isLow
     * @return array
     */
    public function top($limit = 5, $isLow = false)
    {
        $visitsIds  = $this->getVisits($limit, $this->keys->visits, $isLow);
        $cacheKey   = $this->keys->cache($limit, $isLow);
        $cachedList = $this->cachedList($limit, $cacheKey);
        $cachedIds  = $cachedList->pluck('id')->toArray();

        return ($visitsIds === $cachedIds && ! $this->fresh) ? $cachedList : $this->freshList($cacheKey, $visitsIds);
    }

    /**
     * Fetch lowest subjects.
     *
     * @param int $limit
     * @return array
     */
    public function low($limit = 5)
    {
        return $this->top($limit, true);
    }

    /**
     * Check for the ip is has been recorded before
     *
     * @return bool
     * @internal param $subject
     */
    public function recordedIp()
    {
        return ! Redis::set($this->keys->ip(request()->ip()), true, 'EX', $this->ipSeconds, 'NX');
    }

    /**
     * Get visits of model instance.
     *
     * @return mixed
     * @internal param $subject
     */
    public function count()
    {
        return intval(
            ( ! $this->keys->instanceOfModel) ?
                Redis::get($this->keys->visits . '_total') :
                Redis::zscore($this->keys->visits, $this->keys->id)
        );
    }

    /**
     * use diffForHumans to show diff
     * @param $period
     * @return Carbon
     */
    public function timeLeft($period = false)
    {
        return Carbon::now()->addSeconds(Redis::ttl(
                    $period ? $this->keys->period($period) : $this->keys->ip( request()->ip() )
                ));
    }

    /**
     * Increment a new/old subject to the cache cache.
     *
     * @param int $inc
     * @param bool $force
     * @param bool $periods
     */
    public function increment($inc = 1, $force = false, $periods = true)
    {
        if ( $force || ! $this->recordedIp()) {
            Redis::zincrby($this->keys->visits, $inc, $this->keys->id);
            Redis::incrby($this->keys->visits . '_total', $inc);


            if($periods) {
                foreach ($this->periods as $period => $days) {
                    $periodKey = $this->keys->period($period);

                    if ( ! Redis::exists($periodKey)) {
                        Redis::zincrby($periodKey, $inc, $this->keys->id);
                        Redis::expire($periodKey, $days * 24 * 60 * 60);
                    } else {
                        Redis::zincrby($periodKey, $inc, $this->keys->id);
                    }

                    Redis::incrby($periodKey . '_total', $inc);
                }
            }
        }
    }


    /**
     * @param int $inc
     * @param bool $periods
     */
    public function forceIncrement($inc = 1, $periods = true)
    {
        $this->increment($inc, true, $periods);
    }

    /**
     * Decrement a new/old subject to the cache cache.
     *
     * @param int $dec
     * @param bool $force
     */
    public function decrement($dec = 1, $force = false)
    {
        $this->increment(-$dec, $force);
    }

    /**
     * @param int $dec
     * @param bool $periods
     */
    public function forceDecrement($dec = 1, $periods = true)
    {
        $this->increment(-$dec, true, $periods);
    }

    /**
     * @param $limit
     * @param $visitsKey
     * @param bool $isLow
     * @return mixed
     */
    protected function getVisits($limit, $visitsKey, $isLow = false)
    {
        $range = $isLow ? 'zrange' : 'zrevrange';

        return array_map('intval', Redis::$range($visitsKey, 0, $limit - 1));
    }

    /**
     * @param $cacheKey
     * @param $visitsIds
     * @return mixed
     */
    protected function freshList($cacheKey, $visitsIds)
    {
        if (count($visitsIds)) {
            Redis::del($cacheKey);

            return ($this->subject)::whereIn($this->keys->primary, $visitsIds)
                //->orderByRaw(DB::raw("FIELD({$this->keys->primary}, " . implode(',', $visitsIds) . ")"))
                ->get()
                ->sortBy(function($subject) use($visitsIds) {
                    return array_search($subject->{$this->keys->primary}, $visitsIds);
                })
                ->each(function ($subject) use ($cacheKey) {
                    Redis::rpush($cacheKey, serialize($subject));
                });
        }

        return [];
    }

    /**
     * @param $limit
     * @param $cacheKey
     * @return array
     */
    protected function cachedList($limit, $cacheKey)
    {
        return collect(array_map('unserialize', Redis::lrange($cacheKey, 0, $limit - 1)));
    }
}
