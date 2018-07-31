<?php

namespace if4lcon\Bareq;

use Illuminate\Support\Facades\Redis;

class Reset extends Visits
{
    protected $visits;

    /**
     * Reset constructor.
     * @param Visits $parent
     * @param Keys $keys
     * @param $method
     * @param $args
     */
    public function __construct(Visits $parent, $method, $args)
    {
        parent::__construct($parent->subject);
        $this->keys = $parent->keys;

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
        $this->allcountries();
        $this->allrefs();
    }

    /**
     * reset all time visits
     */
    public function visits()
    {
        if ($this->keys->id) {
            $this->redis->zrem($this->keys->visits, $this->keys->id);
            $this->redis->del($this->keys->visits . "_countries:{$this->keys->id}");
            $this->redis->del($this->keys->visits . "_referers:{$this->keys->id}");

            foreach ($this->periods as $period => $days) {
                $this->redis->zrem($this->keys->period($period), $this->keys->id);
            }

            $this->ips();
        } else {
            $this->redis->del($this->keys->visits);
            $this->redis->del($this->keys->visits . '_total');
        }

    }

    public function allrefs()
    {
        $cc = $this->redis->keys($this->keys->visits . '_referers:*');

        if (count($cc)) {
            $this->redis->del($cc);
        }
    }


    public function allcountries()
    {
        $cc = $this->redis->keys($this->keys->visits . '_countries:*');

        if (count($cc)) {
            $this->redis->del($cc);
        }
    }

    /**
     * reset day,week counters
     */
    public function periods()
    {
        foreach ($this->periods as $period => $days) {
            $periodKey = $this->keys->period($period);
            $this->redis->del($periodKey);
            $this->redis->del($periodKey . '_total');
        }
    }

    /**
     * reset ips protection
     * @param string $ips
     */
    public function ips($ips = '*')
    {
        $ips = $this->redis->keys($this->keys->ip($ips));

        if (count($ips)) {
            $this->redis->del($ips);
        }
    }

    /**
     * reset lists top/low
     */
    public function lists()
    {
        $lists = $this->redis->keys($this->keys->cache());
        if (count($lists)) {
            $this->redis->del($lists);
        }
    }
}
