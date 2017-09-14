<?php

if (! function_exists('visits'))
{
    function visits($subject)
    {
        return new \if4lcon\Bareq\Visits($subject);
    }
}
