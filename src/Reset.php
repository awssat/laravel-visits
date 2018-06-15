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
    public function __construct(Visits $parent,$method, $args)
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
            Redis::zrem($this->keys->visits, $this->keys->id);
            Redis::del($this->keys->visits . "_countries:{$this->keys->id}");
            Redis::del($this->keys->visits . "_referers:{$this->keys->id}");

            foreach ($this->periods as $period => $days) {
                Redis::zrem($this->keys->period($period), $this->keys->id);
            }

            $this->ips();
        } else {
            Redis::del($this->keys->visits);
            Redis::del($this->keys->visits . '_total');
        }

    }

    public function allrefs()
    {
        $cc = Redis::keys($this->keys->visits . '_referers:*');

        if (count($cc)) {
            Redis::del($cc);
        }
    }


    public function allcountries()
    {
        $cc = Redis::keys($this->keys->visits . '_countries:*');

        if (count($cc)) {
            Redis::del($cc);
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
