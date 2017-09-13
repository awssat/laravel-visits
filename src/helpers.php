<?php

if (! function_exists('visits'))
{
    function visits($subject)
    {
        return new \phpfalcon\Bareq\Visits($subject);
    }
}
