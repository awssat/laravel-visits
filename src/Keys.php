<?php

namespace awssat\Visits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Keys
{
    public $prefix;
    public $testing = '';
    public $modelName = false;
    public $id;
    public $visits;
    public $primary = 'id';
    public $instanceOfModel = false;
    public $tag;

    /**
     * Keys constructor.
     * @param $subject
     * @param $tag
     */
    public function __construct($subject, $tag)
    {
        $this->modelName = $this->pluralModelName($subject);
        $this->prefix = config('visits.redis_keys_prefix');
        $this->testing = app()->environment('testing') ? 'testing:' : '';
        $this->primary = (new $subject)->getKeyName();
        $this->tag = $tag;
        $this->visits = $this->visits();

        if ($subject instanceof Model) {
            $this->instanceOfModel = true;
            $this->modelName = $this->modelName($subject);
            $this->id = $subject->{$subject->getKeyName()};
        }
    }

    /**
     * Get cache key
     *
     * @return string
     */
    public function visits()
    {
        return "{$this->prefix}:$this->testing" . $this->modelName . "_{$this->tag}";
    }

    /**
     * Get cache key
     *
     * @return string
     */
    public function visitsTotal()
    {
        return "{$this->visits}_total";
    }

    /**
     * @param $ip
     * @return string
     */
    public function ip($ip)
    {
        return $this->visits . '_' .
            Str::snake("recorded_ips:" . ($this->instanceOfModel ? "{$this->id}:" : '') . $ip);
    }


    /**
     * @param $limit
     * @param $isLow
     * @return string
     */
    public function cache($limit = '*', $isLow = false)
    {
        $key = $this->visits . "_lists";

        if ($limit == '*') {
            return "{$key}:*";
        }

        return "{$key}:" . ($isLow ? "low" : "top") . "{$limit}";
    }

    /**
     * @param $period
     * @return string
     */
    public function period($period)
    {
        return "{$this->visits}_{$period}";
    }

    /**
     * @param $relation
     * @param $id
     */
    public function append($relation, $id)
    {
        $this->visits .= "_{$relation}_{$id}";
    }

    /**
     * @param $subject
     * @return string
     */
    public function modelName($subject)
    {
        return strtolower(Str::singular(class_basename(get_class($subject))));
    }

    /**
     * @param $subject
     * @return string
     */
    public function pluralModelName($subject)
    {
        return strtolower(Str::plural(class_basename(is_string($subject) ? $subject : get_class($subject))));
    }
}
