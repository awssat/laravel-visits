<?php

namespace Awssat\Visits\Tests\Feature;


use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentPeriodsTest extends PeriodsTestCase
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

    /** @test */
    public function it_can_get_visits_for_a_date_range_with_daily_visits_method()
    {
        $post = \Awssat\Visits\Tests\Post::create();

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2023-01-01'));
        visits($post)->increment();

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2023-01-02'));
        visits($post)->increment();

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2023-01-03'));
        visits($post)->increment();

        $this->assertEquals(2, visits($post)->dailyVisits('2023-01-01', '2023-01-02'));
    }
}
