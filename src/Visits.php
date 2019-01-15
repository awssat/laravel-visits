<?php

namespace awssat\Visits;

use awssat\Visits\Traits\Lists;
use awssat\Visits\Traits\Periods;
use awssat\Visits\Traits\Record;
use awssat\Visits\Traits\Setters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Visits
{
    use Record, Lists, Periods, Setters;

    /**
     * @var mixed
     */
    protected $ipSeconds;
    /**
     * @var null
     */
    protected $subject;
    /**
     * @var bool|mixed
     */
    protected $fresh = false;
    /**
     * @var null
     */
    protected $country = null;
    /**
     * @var null
     */
    protected $referer = null;
    /**
     * @var mixed
     */
    protected $periods;
    /**
     * @var Keys
     */
    protected $keys;
    /**
     * @var Redis
     */
    public $redis;
    /**
     * @var boolean
     */
    public $ignoreCrawlers = false;

    /**
     * Visits constructor.
     * @param $subject
     * @param string $tag|null
     */
    public function __construct($subject = null, $tag = 'visits')
    {
        $config = config('visits');
        $this->redis = Redis::connection($config['connection']);
        $this->periods = $config['periods'];
        $this->ipSeconds = $config['remember_ip'];
        $this->fresh = $config['always_fresh'];
        $this->ignoreCrawlers = $config['ignore_crawlers'];
        $this->subject = $subject;
        $this->keys = new Keys($subject, $tag);

        $this->periodsSync();
    }

    /**
     * @param $subject
     * @return $this
     */
    public function by($subject)
    {
        if($subject instanceof Model) {
            $this->keys->append($this->keys->modelName($subject), $subject->{$subject->getKeyName()});
        } else if (is_array($subject)) {
            $this->keys->append(array_keys($subject)[0], array_first($subject));
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
        return new Reset($this, $method, $args);
    }

    /**
     * Check for the ip is has been recorded before
     *
     * @return bool
     * @internal param $subject
     */
    public function recordedIp()
    {
        return ! $this->redis->set($this->keys->ip(request()->ip()), true, 'EX', $this->ipSeconds, 'NX');
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
            return $this->redis->zscore($this->keys->visits . "_countries:{$this->keys->id}", $this->country);
        } else if ($this->referer) {
            return $this->redis->zscore($this->keys->visits . "_referers:{$this->keys->id}", $this->referer);
        }

        return intval(
            (!$this->keys->instanceOfModel) ?
                $this->redis->get($this->keys->visits . '_total') :
                $this->redis->zscore($this->keys->visits, $this->keys->id)
        );
    }

    /**
     * use diffForHumans to show diff
     * @param $period
     * @return Carbon
     */
    public function timeLeft($period = false)
    {
        return Carbon::now()->addSeconds($this->redis->ttl(
            $period ? $this->keys->period($period) : $this->keys->ip(request()->ip())
        ));
    }

    protected function isCrawler()
    {
        return $this->ignoreCrawlers && app(CrawlerDetect::class)->isCrawler();
    }

    /**
     * Increment a new/old subject to the cache.
     *
     * @param int $inc
     * @param bool $force
     * @param bool $periods
     * @param bool $country
     * @param bool $refer
     */
    public function increment($inc = 1, $force = false, $periods = true, $country = true, $refer = true)
    {
        if ($force OR !$this->isCrawler() && !$this->recordedIp()) {
            $this->redis->zincrby($this->keys->visits, $inc, $this->keys->id);
            $this->redis->incrby($this->keys->visits . '_total', $inc);

            //NOTE: $method is parameter also .. ($periods,$country,$refer)
            foreach (['country', 'refer', 'periods'] as $method) {
                $$method && $this->{'record' . studly_case($method)}($inc);
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
     * @param $period
     * @param int $time
     * @return bool
     */
    public function expireAt($period, $time)
    {
        $periodKey = $this->keys->period($period);
        return $this->redis->expire($periodKey, $time);
    }
}
