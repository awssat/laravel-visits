<?php

namespace awssat\Visits;

use awssat\Visits\Traits\Lists;
use awssat\Visits\Traits\Periods;
use awssat\Visits\Traits\Record;
use awssat\Visits\Traits\Setters;
use Illuminate\Support\Carbon;
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
     * @var null|string
     */
    protected $country = null;
    /**
     * @var null|string
     */
    protected $referer = null;
    /**
     * @var null|string
     */
    protected $operatingSystem = null;
    /**
     * @var null|string
     */
    protected $language = null;
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
        } else if ($this->operatingSystem) {
            return $this->redis->zscore($this->keys->visits . "_OSes:{$this->keys->id}", $this->operatingSystem);
        } else if ($this->language) {
            return $this->redis->zscore($this->keys->visits . "_languages:{$this->keys->id}", $this->language);
        }

        return intval(
            $this->keys->instanceOfModel ?
                $this->redis->zscore($this->keys->visits, $this->keys->id) :
                $this->redis->get($this->keys->visitsTotal())
        );
    }

    /**
     * use diffForHumans to show diff
     * @return Carbon
     */
    public function timeLeft()
    {
        return Carbon::now()->addSeconds($this->redis->ttl($this->keys->visits));
    }

    /**
     * use diffForHumans to show diff
     * @return Carbon
     */
    public function ipTimeLeft()
    {
        return Carbon::now()->addSeconds($this->redis->ttl($this->keys->ip(request()->ip())));
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
     * @param array $ignore to ignore recording visits of periods, country, refer, language and operatingSystem. pass them on this array.
     * @param bool $refer
     * @param bool $operatingSystem
     */
    public function increment($inc = 1, $force = false, $ignore = [])
    {
        if ($force || (!$this->isCrawler() && !$this->recordedIp())) {
            $this->redis->zincrby($this->keys->visits, $inc, $this->keys->id);
            $this->redis->incrby($this->keys->visitsTotal(), $inc);

            //NOTE: $$method is parameter also .. ($periods,$country,$refer)
            foreach (['country', 'refer', 'periods', 'operatingSystem', 'language'] as $method) {
                if(! in_array($method, $ignore))  {
                    $this->{'record'.studly_case($method)}($inc);
                }
            }
        }
    }

    /**
     * @param int $inc
     * @param array $ignore to ignore recording visits like country, periods ...
     */
    public function forceIncrement($inc = 1, $ignore = [])
    {
        $this->increment($inc, true, $ignore);
    }

    /**
     * Decrement a new/old subject to the cache cache.
     *
     * @param int $dec
     * @param array $ignore to ignore recording visits like country, periods ...
     */
    public function decrement($dec = 1, $force = false, $ignore = [])
    {
        $this->increment(-$dec, $force, $ignore);
    }

    /**
     * @param int $dec
     * @param array $ignore to ignore recording visits like country, periods ...
     */
    public function forceDecrement($dec = 1, $ignore = [])
    {
        $this->decrement($dec, true, $ignore);
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
