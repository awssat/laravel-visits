<?php

namespace awssat\Visits\Traits;

use Carbon\Carbon;
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
            $periodCarbon = $this->xHoursPeriod($period) ??
                Carbon::now()->{'endOf' . studly_case($period)}();
        } catch (Exception $exception) {
            throw new Exception('Wrong period : ' . $period .
                ' please update your visits.php config');
        }

        return $periodCarbon->diffInSeconds() + 1;
    }

    /**
     * @param $period
     * @return mixed
     */
    protected function xHoursPeriod($period)
    {
        return collect(range(1, 12))->map(function ($hour) {
                return ['method' => $hour . 'hours', 'hours' => $hour];
            })->where('method', $period)
            ->pluck('hours')
            ->map(function ($hours) {
                return Carbon::now()->endOfxHours($hours);
            })
            ->first();
    }
}
