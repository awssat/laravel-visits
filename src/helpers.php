<?php

if (! function_exists('visits'))
{
    function visits($subject, $tag = 'visits')
    {
        return new \if4lcon\Bareq\Visits($subject, $tag);
    }
}
