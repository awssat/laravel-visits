<?php

namespace if4lcon\Bareq;

use Carbon\Carbon;
use function Composer\Autoload\includeFile;
use Illuminate\Support\Facades\Redis;
use Spatie\Referer\Referer;

class Visits
{
    protected $ipSeconds;
    protected $subject;
    protected $fresh = false;
    protected $country = null;
    protected $referer = null;
    protected $periods;
    protected $keys;

    /**
     * Visits constructor.
     * @param $subject
     * @param Keys|null $keys
     */
    public function __construct($subject = null, $tag = 'visits')
    {
        $config = config('bareq');
        $this->periods = $config['periods'];
        $this->ipSeconds = $config['remember_ip'];
        $this->fresh = $config['always_fresh'];
        $this->subject = $subject;
        $this->keys = new Keys($subject, $tag);

        $this->periodsSync();
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


    public function country($country)
    {
        $this->country = $country;

        return $this;
    }


    public function referer($referer)
    {
        $this->referer = $referer;

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

    protected function periodsSync()
    {
        foreach ($this->periods as $period) {
            $periodKey = $this->keys->period($period);

            if ($this->noExpiration($periodKey)) {
                $expireInSeconds = $this->newExpiration($period);
                Redis::incrby($periodKey . '_total', 0);
                Redis::zincrby($periodKey, 0, 0);
                Redis::expire($periodKey, $expireInSeconds);
                Redis::expire($periodKey . '_total', $expireInSeconds);
            }
        }
    }

    protected function noExpiration($periodKey)
    {
        return Redis::ttl($periodKey) == -1 || !Redis::exists($periodKey);
    }

    protected function newExpiration($period)
    {
        $expireInSeconds = 0;

        switch ($period) {
            case 'day':
                $expireInSeconds = Carbon::now()->endOfDay()->timestamp - Carbon::now()->timestamp;
                break;
            case 'week':
                $expireInSeconds = Carbon::now()->endOfWeek()->timestamp - Carbon::now()->timestamp;
                break;
            case 'month':
                $expireInSeconds = Carbon::now()->endOfMonth()->timestamp - Carbon::now()->timestamp;
                break;
            case 'year':
                $expireInSeconds = Carbon::now()->endOfYear()->timestamp - Carbon::now()->timestamp;
                break;
        }

        return $expireInSeconds + 1;
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
        return new Reset($this, $method, $args);
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
        $visitsIds = $this->getVisits($limit, $this->keys->visits, $isLow);
        $cacheKey = $this->keys->cache($limit, $isLow);
        $cachedList = $this->cachedList($limit, $cacheKey);
        $cachedIds = $cachedList->pluck('id')->toArray();

        return ($visitsIds === $cachedIds && !$this->fresh) ? $cachedList : $this->freshList($cacheKey, $visitsIds);
    }


    public function countries($limit = -1, $isLow = false)
    {
        $range = $isLow ? 'zrange' : 'zrevrange';

        return Redis::$range($this->keys->visits . "_countries:{$this->keys->id}", 0, $limit, 'WITHSCORES');
    }

    public function refs($limit = -1, $isLow = false)
    {
        $range = $isLow ? 'zrange' : 'zrevrange';

        return Redis::$range($this->keys->visits . "_referers:{$this->keys->id}", 0, $limit, 'WITHSCORES');
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
        return !Redis::set($this->keys->ip(request()->ip()), true, 'EX', $this->ipSeconds, 'NX');
    }

    /**
     * Get visits of model instance.
     *
     * @return mixed
     * @internal param $subject
     */
    public function count()
    {
        if ($this->country) {
            return Redis::zscore($this->keys->visits . "_countries:{$this->keys->id}", $this->country);
        } else if ($this->referer) {
            return Redis::zscore($this->keys->visits . "_referers:{$this->keys->id}", $this->referer);
        }

        return intval(
            (!$this->keys->instanceOfModel) ?
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
            $period ? $this->keys->period($period) : $this->keys->ip(request()->ip())
        ));
    }


    /**
     * Increment a new/old subject to the cache cache.
     *
     * @param int $inc
     * @param bool $force
     * @param bool $periods
     * @param bool $country
     * @param bool $refer
     */
    public function increment($inc = 1, $force = false, $periods = true, $country = true, $ip = null, $refer = true)
    {
        if ($force || !$this->recordedIp()) {
            Redis::zincrby($this->keys->visits, $inc, $this->keys->id);
            Redis::incrby($this->keys->visits . '_total', $inc);


            if ($country) {
                $zz = $this->getCountry($ip);
                Redis::zincrby($this->keys->visits . "_countries:{$this->keys->id}", $inc, $zz);
            }


            $referer = app(Referer::class)->get();

            if ($refer && !empty($referer)) {
                Redis::zincrby($this->keys->visits . "_referers:{$this->keys->id}", $inc, $referer);
            }

            if ($periods) {
                foreach ($this->periods as $period) {
                    $periodKey = $this->keys->period($period);

                    Redis::zincrby($periodKey, $inc, $this->keys->id);
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
                ->get()
                ->sortBy(function ($subject) use ($visitsIds) {
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


    /**
     *  Gets visitor country code
     * @return mixed|string
     */
    public function getCountry($ip = null)
    {
        if (session('country_code')) {
            return session('country_code');
        }

        $country_code = 'zz';

        if (isset($_SERVER["HTTP_CF_IPCOUNTRY"])) {
            $country_code = strtolower($_SERVER["HTTP_CF_IPCOUNTRY"]);
        }

        if ($country_code === 'zz' && app()->has('geoip')) {

            $geo_info = geoip()->getLocation($ip);

            if (!empty($geo_info) && isset($geo_info['iso_code'])) {
                $country_code = strtolower($geo_info['iso_code']);
            }

        }

        session(['country_code', $country_code]);
        return $country_code;
    }

    /**
     * @param $period
     * @param int $time
     * @return bool
     */
    public function expireAt($period, $time)
    {
        $periodKey = $this->keys->period($period);
        return Redis::expire($periodKey, $time);
    }
}
