<?php

namespace Awssat\Visits\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;

class EloquentVisitsTest extends VisitsTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['visits.engine'] = 'eloquent';
        $this->connection = app(\Awssat\Visits\DataEngines\EloquentEngine::class)
                            ->setPrefix($this->app['config']['visits.keys_prefix']);
        include_once __DIR__.'/../../database/migrations/create_visits_table.php.stub';
        (new \CreateVisitsTable())->up();
    }
}
