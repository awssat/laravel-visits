<?php

namespace if4lcon\Bareq;

use Illuminate\Database\Eloquent\Model;

class Keys
{
    public $prefix;
    public $testing = '';
    public $modelName = false;
    public $id;
    public $visits;
    public $primary = 'id';
    public $instanceOfModel = false;

    /**
     * Keys constructor.
     * @param $subject
     */
    public function __construct($subject)
    {
        $this->modelName =  strtolower(str_plural(class_basename(is_string($subject) ? $subject : get_class($subject))));
        $this->prefix    = config('bareq.redis_keys_prefix');
        $this->testing   = app()->environment('testing') ? 'testing:' : '';
        $this->primary   = (new $subject)->getKeyName();
        $this->visits    = $this->visits($subject);

        if ($subject instanceof Model) {
            $this->instanceOfModel = true;
            $this->modelName = strtolower(str_singular(class_basename(get_class($subject))));
            $this->id        = $subject->{$subject->getKeyName()};
        }
    }

    /**
     * Get cache key
     *
     * @param $key
     * @return string
     */
    public function visits($key)
    {
        return "{$this->prefix}:$this->testing"  .
            strtolower(str_plural(class_basename(is_string($key) ? $key : get_class($key)))) .
            '_visits';
    }

    /**
     * @param $ip
     * @return string
     */
    public function ip($ip)
    {
        return "{$this->prefix}:$this->testing" . snake_case("recorded_ips:" . ($this->instanceOfModel ? "{$this->modelName}_{$this->id}:" : '') . $ip);
    }


    /**
     * @param $limit
     * @param $isLow
     * @return string
     */
    public function cache($limit = '*', $isLow = false)
    {
        $key = "{$this->prefix}:$this->testing" . "lists";
        if($limit == '*') {
            return "{$key}:*";
        }
        return "{$key}:" . ($isLow ? "low" : "top") . "{$limit}_{$this->modelName}";
    }

    /**
     * @param $period
     * @return string
     */
    public function period($period)
    {
        return  "{$this->visits}_{$period}";
    }
}
