<?php

if (! function_exists('visits'))
{
    function visits($subject, $tag = 'visits')
    {
        return new \Awssat\Visits\Visits($subject, $tag);
    }
}
