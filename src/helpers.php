<?php

if (! function_exists('visits'))
{
    function visits($subject, $tag = 'visits')
    {
        return new \awssat\Visits\Visits($subject, $tag);
    }
}
