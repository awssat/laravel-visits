<?php

namespace Awssat\Visits\Commands;

use Awssat\Visits\Models\Visit;
use Illuminate\Console\Command;
use Awssat\Visits\DataEngines\EloquentEngine;

class CleanCommand extends Command
{
    protected $signature = 'visits:clean';
    protected $description = '(Laravel-Visits) Clean expired keys and visits.';


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currentEngine = config('visits.engine') ?? '';

        if($currentEngine == EloquentEngine::class || is_subclass_of($currentEngine, EloquentEngine::class)) {
            $this->cleanEloquent();
        }
    }

    protected function cleanEloquent()
    {
        Visit::where('expired_at', '<', \Carbon\Carbon::now())->delete();
    }
}
