<?php

namespace if4lcon\Bareq;

use Illuminate\Support\Facades\Redis;

class Reset extends Visits
{

    /**
     * Reset constructor.
     * @param Visits $parent
     * @param Keys $keys
     * @param $method
     * @param $args
     */
    public function __construct(Visits $parent, Keys $keys, $method, $args)
    {
        parent::__construct($parent->subject, $keys);

        if (method_exists($this, $method)) {
            if (empty($args)) {
                $this->$method();
            } else {
                $this->$method($args);
            }
        }
    }

    /**
     * Reset everything
     *
     */
    public function factory()
    {
        $this->visits();
        $this->periods();
        $this->ips();
        $this->lists();
    }

    /**
     * reset all time visits
     */
    public function visits()
    {
        if ($this->keys->id) {
            $this->forceDecrement($this->count());
        } else {
            Redis::del($this->keys->visits);
            Redis::del($this->keys->visits . '_total');
        }

    }

    /**
     * reset day,week counters
     */
    public function periods()
    {
        foreach ($this->periods as $period => $days) {
            $periodKey = $this->keys->period($period);
            Redis::del($periodKey);
            Redis::del($periodKey . '_total');
        }
    }

    /**
     * reset ips protection
     * @param string $ips
     */
    public function ips($ips = '*')
    {
        $ips = Redis::keys($this->keys->ip($ips));

        if (count($ips)) {
            Redis::del($ips);
        }
    }

    /**
     * reset lists top/low
     */
    public function lists()
    {
        $lists = Redis::keys($this->keys->cache());
        if (count($lists)) {
            Redis::del($lists);
        }
    }
}
