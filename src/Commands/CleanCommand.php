<?php

namespace Awssat\Visits\Commands;

use Awssat\Visits\Models\Visit;
use Illuminate\Console\Command;

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

        if($currentEngine == 'eloquent') {
            $this->cleanEloquent();
        }
    }

    protected function cleanEloquent()
    {
        Visit::where('expired_at', '<', \Carbon\Carbon::now())->delete();
    }
}
