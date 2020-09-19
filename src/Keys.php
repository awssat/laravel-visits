<?php

namespace Awssat\Visits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Keys
{
    public $modelName = false;
    public $id;
    public $visits;
    public $primary = 'id';
    public $instanceOfModel = false;
    public $tag;

    public function __construct($subject, $tag)
    {
        $this->modelName = $this->pluralModelName($subject);
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
     */
    public function visits()
    {
        return (app()->environment('testing') ? 'testing:' : '').$this->modelName."_{$this->tag}";
    }

    /**
     * Get cache key for total values
     */
    public function visitsTotal()
    {
        return "{$this->visits}_total";
    }

    /**
     * ip key
     */
    public function ip($ip)
    {
        return $this->visits.'_'.Str::snake(
            'recorded_ips:'.($this->instanceOfModel ? "{$this->id}:" : '') . $ip
        );
    }

    /**
     * list cache key
     */
    public function cache($limit = '*', $isLow = false, $constraints = [])
    {
        $key = $this->visits.'_lists';

        if ($limit == '*') {
            return "{$key}:*";
        }

        //it might not be that unique but it does the job since not many lists
        //will be generated to one key.
        $constraintsPart = count($constraints) ? ':'.substr(sha1(serialize($constraints)), 0, 7) : '';

        return "{$key}:".($isLow ? 'low' : 'top').$constraintsPart.$limit;
    }

    /**
     * period key
     */
    public function period($period)
    {
        return "{$this->visits}_{$period}";
    }

    public function append($relation, $id)
    {
        $this->visits .= "_{$relation}_{$id}";
    }

    public function modelName($subject)
    {
        return strtolower(Str::singular(class_basename(get_class($subject))));
    }

    public function pluralModelName($subject)
    {
        return strtolower(Str::plural(class_basename(is_string($subject) ? $subject : get_class($subject))));
    }
}
