<?php

namespace awssat\Visits\Traits;

use Spatie\Referer\Referer;

trait Record
{
    /**
     * @param $inc
     */
    protected function recordCountry($inc)
    {
        $this->redis->zincrby($this->keys->visits . "_countries:{$this->keys->id}", $inc, $this->getVisitorCountry());
    }

    /**
     * @param $inc
     */
    protected function recordRefer($inc)
    {
        $referer = app(Referer::class)->get();
        $this->redis->zincrby($this->keys->visits . "_referers:{$this->keys->id}", $inc, $referer);
    }

    /**
     * @param $inc
     */
    protected function recordPeriods($inc)
    {
        foreach ($this->periods as $period) {
            $periodKey = $this->keys->period($period);

            $this->redis->zincrby($periodKey, $inc, $this->keys->id);
            $this->redis->incrby($periodKey . '_total', $inc);
        }
    }

    /**
     *  Gets visitor country code
     * @return mixed|string
     */
    protected function getVisitorCountry()
    {
        return strtolower(geoip()->getLocation()->iso_code);
    }
}
