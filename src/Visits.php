<?php

namespace Awssat\Visits;

use Awssat\Visits\Traits\{Lists, Periods, Record, Setters};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
     * @var \Awssat\DataEngines\DataEngine
     */
    protected $connection;

    /**
     * @var boolean
     */
    public $ignoreCrawlers = false;
    /**
     * @var array
     */
    public $globalIgnore = [];

    /**
     * @param \Illuminate\Database\Eloquent\Model $subject any model
     * @param string $tag use only if you want to use visits on multiple models
     */
    public function __construct($subject = null, $tag = 'visits')
    {
        $config = config('visits');

        $this->connection = $this->determineConnection($config['engine'] ?? 'redis')
                                ->connect($config['connection'])
                                ->setPrefix($config['keys_prefix'] ?? $config['redis_keys_prefix'] ?? 'visits');

        if(! $this->connection) {
            return;
        }

        $this->periods = $config['periods'];
        $this->ipSeconds = $config['remember_ip'];
        $this->fresh = $config['always_fresh'];
        $this->ignoreCrawlers = $config['ignore_crawlers'];
        $this->globalIgnore = $config['global_ignore'];
        $this->subject = $subject;
        $this->keys = new Keys($subject, preg_replace('/[^a-z0-9_]/i', '', $tag));

        $this->periodsSync();
    }

    protected function determineConnection($name)
    {
        $connections = [
            'redis' => \Awssat\Visits\DataEngines\RedisEngine::class,
            'eloquent' => \Awssat\Visits\DataEngines\EloquentEngine::class
        ];

        if(! array_key_exists($name, $connections)) {
            throw new \Exception("(Laravel-Visits) The selected engine `{$name}` is not supported! Please correct this issue from config/visits.php.");
        }

        return app($connections[$name]);
    }

    /**
     * @param $subject
     * @return self
     */
    public function by($subject)
    {
        if($subject instanceof Model) {
            $this->keys->append($this->keys->modelName($subject), $subject->{$subject->getKeyName()});
        } else if (is_array($subject)) {
            $this->keys->append(array_keys($subject)[0], Arr::first($subject));
        }

        return $this;
    }

    /**
     * Reset methods
     *
     * @param $method
     * @param string $args
     * @return \Awssat\Visits\Reset
     */
    public function reset($method = 'visits', $args = '')
    {
        return new Reset($this, $method, $args);
    }

    /**
     * Check for the ip is has been recorded before
     * @return bool
     */
    public function recordedIp()
    {
        if(! $this->connection->exists($this->keys->ip(request()->ip()))) {
            $this->connection->set($this->keys->ip(request()->ip()), true);
            $this->connection->setExpiration($this->keys->ip(request()->ip()), $this->ipSeconds);

            return false;
        }

        return true;
    }

    /**
     * Get visits of model incount(stance.
     * @return mixed
     */
    public function count()
    {
        if ($this->country) {
            return $this->connection->get($this->keys->visits."_countries:{$this->keys->id}", $this->country);
        } else if ($this->referer) {
            return $this->connection->get($this->keys->visits."_referers:{$this->keys->id}", $this->referer);
        } else if ($this->operatingSystem) {
            return $this->connection->get($this->keys->visits."_OSes:{$this->keys->id}", $this->operatingSystem);
        } else if ($this->language) {
            return $this->connection->get($this->keys->visits."_languages:{$this->keys->id}", $this->language);
        }

        return intval(
            $this->keys->instanceOfModel
                    ? $this->connection->get($this->keys->visits, $this->keys->id)
                    : $this->connection->get($this->keys->visitsTotal())
        );
    }

    /**
     * @return integer time left in seconds
     */
    public function timeLeft()
    {
        return $this->connection->timeLeft($this->keys->visits);
    }

    /**
     * @return integer time left in seconds
     */
    public function ipTimeLeft()
    {
        return $this->connection->timeLeft($this->keys->ip(request()->ip()));
    }

    protected function isCrawler()
    {
        return $this->ignoreCrawlers && app(CrawlerDetect::class)->isCrawler();
    }

    /**
     * @param int $inc value to increment
     * @param bool $force force increment, skip time limit
     * @param array $ignore to ignore recording visits of periods, country, refer, language and operatingSystem. pass them on this array.
     */
    public function increment($inc = 1, $force = false, $ignore = [])
    {
        if ($force || (!$this->isCrawler() && !$this->recordedIp())) {
            $this->connection->increment($this->keys->visits, $inc, $this->keys->id);
            $this->connection->increment($this->keys->visitsTotal(), $inc);

            if(is_array($this->globalIgnore) && sizeof($this->globalIgnore) > 0) {
                $ignore = array_merge($ignore, $this->globalIgnore);
            }

            //NOTE: $$method is parameter also .. ($periods,$country,$refer)
            foreach (['country', 'refer', 'periods', 'operatingSystem', 'language'] as $method) {
                if(! in_array($method, $ignore))  {
                    $this->{'record'.Str::studly($method)}($inc);
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
        return $this->connection->setExpiration($periodKey, $time);
    }
}
