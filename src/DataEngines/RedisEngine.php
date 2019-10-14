<?php
namespace Awssat\Visits\DataEngines;


use Illuminate\Contracts\Redis\Factory;

class RedisEngine implements DataEngine
{
    private $redis = null;
    private $connection = null;
    private $prefix = null;

    public function __construct(Factory $redis)
    {
        $this->redis = $redis;
    }

    public function connect(string $connection): DataEngine
    {
        $this->connection = $this->redis->connection($connection);
        return $this;
    }

    public function setPrefix(string $prefix): DataEngine
    {
        $this->prefix = $prefix . ':';
        return $this;
    }

    public function increment(string $key, int $value, ?string $member = null): bool
    {
        if (! empty($member) || is_numeric($member)) {
            $this->connection->zincrby($this->prefix.$key, $value, $member);
        } else {
            $this->connection->incrby($this->prefix.$key, $value);
        }
        
        // both methods returns integer and raise an excpetion in case of an error.
        return true;
    }

    public function decrement(string $key, int $value, ?string $member = null): bool
    {
        return $this->increment($key, -$value, $member);
    }

    public function delete($key, ?string $member = null): bool
    {
        if(is_array($key)) {
            array_walk($key, function($item) {
                $this->delete($item);
            });
            return true;
        }

        if(! empty($member) || is_numeric($member)) {
            return $this->connection->zrem($this->prefix.$key, $member) > 0;
        } else {
            return $this->connection->del($this->prefix.$key) > 0;
        }
    }

    public function get(string $key, ?string $member = null)
    {
        if(! empty($member) || is_numeric($member)) {
            return $this->connection->zscore($this->prefix.$key, $member);
        } else {
            return $this->connection->get($this->prefix.$key);
        }
    }

    public function set(string $key, $value, ?string $member = null): bool
    {
        if(! empty($member) || is_numeric($member)) {
            return $this->connection->zAdd($this->prefix.$key, $value, $member) > 0;
        } else {
            return (bool) $this->connection->set($this->prefix.$key, $value);
        }
    }

    public function search(string $word, bool $noPrefix = true): array
    {
        return array_map(
            function($item) use($noPrefix) {
                if ($noPrefix && substr($item, 0, strlen($this->prefix)) == $this->prefix) {
                    return substr($item, strlen($this->prefix));
                } 

                return $item;
            }, 
            $this->connection->keys($this->prefix.$word) ?? []
        );
    }

    public function flatList(string $key, int $limit = -1): array
    {
        return $this->connection->lrange($this->prefix.$key, 0, $limit);
    }

    public function addToFlatList(string $key, $value): bool
    {
        return $this->connection->rpush($this->prefix.$key, $value) !== false;
    }

    public function valueList(string $key, int $limit = -1, bool $orderByAsc = false, bool $withValues = false): array
    {
        $range = $orderByAsc ? 'zrange' : 'zrevrange';

        return $this->connection->$range($this->prefix.$key, 0, $limit,  ['withscores' => $withValues]);
    }

    public function exists(string $key): bool
    {
        return (bool) $this->connection->exists($this->prefix.$key);
    }

    public function timeLeft(string $key): int
    {
        return $this->connection->ttl($this->prefix.$key);
    }

    public function setExpiration(string $key, int $time): bool
    {
        return $this->connection->expire($this->prefix.$key, $time);
    }
}