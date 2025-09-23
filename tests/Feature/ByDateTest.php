<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\TestCase;
use Awssat\Visits\Tests\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ByDateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['database.redis.client'] = 'predis';
        $this->app['config']['database.redis.options.prefix'] = '';
        $this->app['config']['database.redis.laravel-visits'] = [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 3,
        ];

        $this->app['config']['visits.engine'] = \Awssat\Visits\DataEngines\RedisEngine::class;
    }

    /** @test */
    public function it_can_get_visits_by_date()
    {
        Carbon::setTestNow(Carbon::create(2023, 1, 1));
        $post = Post::create(['id' => 1]);
        visits($post)->increment();

        Carbon::setTestNow(Carbon::create(2023, 1, 2));
        visits($post)->increment();
        visits($post)->increment();

        $this->assertEquals(1, visits($post)->byDate('2023-01-01')->count());
        $this->assertEquals(2, visits($post)->byDate('2023-01-02')->count());
        $this->assertEquals(3, visits($post)->byDate('2023-01-01', '2023-01-02')->count());
    }
}
