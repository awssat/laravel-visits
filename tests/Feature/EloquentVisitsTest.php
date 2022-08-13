<?php

namespace Awssat\Visits\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentVisitsTest extends VisitsTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['visits.engine'] = \Awssat\Visits\DataEngines\EloquentEngine::class;
        $this->connection = app(\Awssat\Visits\DataEngines\EloquentEngine::class)
                            ->setPrefix($this->app['config']['visits.keys_prefix']);
        include_once __DIR__.'/../../database/migrations/create_visits_table.php.stub';
        (new \CreateVisitsTable())->up();
    }
}
