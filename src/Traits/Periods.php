<?php

namespace awssat\Visits\Traits;

use Illuminate\Support\Carbon;
use Exception;

trait Periods
{
    /**
     * Sync periods times
     */
    protected function periodsSync()
    {
        foreach ($this->periods as $period) {
            $periodKey = $this->keys->period($period);

            if ($this->noExpiration($periodKey)) {
                $expireInSeconds = $this->newExpiration($period);
                $this->redis->incrby($periodKey . '_total', 0);
                $this->redis->zincrby($periodKey, 0, 0);
                $this->redis->expire($periodKey, $expireInSeconds);
                $this->redis->expire($periodKey . '_total', $expireInSeconds);
            }
        }
    }

    /**
     * @param $periodKey
     * @return bool
     */
    protected function noExpiration($periodKey)
    {
        return $this->redis->ttl($periodKey) == -1 || !$this->redis->exists($periodKey);
    }

    /**
     * @param $period
     * @return int
     * @throws Exception
     */
    protected function newExpiration($period)
    {
        try {
            $periodCarbon = $this->xHoursPeriod($period) ?? Carbon::now()->{'endOf' . studly_case($period)}();
        } catch (Exception $e) {
            throw new Exception("Wrong period: `{$period}`! please update config/visits.php file.");
        }

        return $periodCarbon->diffInSeconds() + 1;
    }

    /**
     * @param $period
     * @return mixed
     */
    protected function xHoursPeriod($period)
    {
        preg_match('/([\d]+)\s?([\w]+)/', $period, $match);
        return isset($match[2]) && isset($match[1]) && $match[2] == 'hours' && $match[1] < 12
                ? Carbon::now()->endOfxHours((int) $match[1]) 
                : null;
    }
}
