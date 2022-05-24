<?php

namespace Awssat\Visits\Exceptions;

class InvalidPeriod extends \Exception
{
    public function __construct($period)
    {
        parent::__construct("Invalid period of Laravel-Visits: $period");
    }
}
