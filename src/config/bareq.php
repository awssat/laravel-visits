<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Counters periods
    |--------------------------------------------------------------------------
    |
    | Set time in days for each periods counter , you can leave it blank if you like
    |
    */
    'periods'      => [

        'day'   => 1,
        'week'  => 7,
        'month' => 30,
        'year'  => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis prefix
    |--------------------------------------------------------------------------
    */
    'redis_keys_prefix' =>  'bareq',

    /*
    |--------------------------------------------------------------------------
    | Remember ip for x seconds of time
    |--------------------------------------------------------------------------
    |
    | Prevent counts duplication by remembering each ip has visited the page for x seconds.
    | Visits from same ip will be counted after ip expire
    |
    */
    'remember_ip' => 15 * 60,

    /*
    |--------------------------------------------------------------------------
    | Always make fresh top/low lists
    |--------------------------------------------------------------------------
    */
    'always_fresh' => false,

];

