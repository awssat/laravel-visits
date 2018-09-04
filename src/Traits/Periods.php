<?php

namespace awssat\Visits\Traits;

use Carbon\Carbon;

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
     */
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
}
