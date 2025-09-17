<?php

if (! function_exists('visits'))
{
    function visits($subject, $tag = 'visits')
    {
        if (is_array($tag)) {
            $visits = new \Illuminate\Support\Collection();
            foreach ($tag as $t) {
                $visits->push(new \Awssat\Visits\Visits($subject, $t));
            }
            return $visits;
        }

        return new \Awssat\Visits\Visits($subject, $tag);
    }
}
