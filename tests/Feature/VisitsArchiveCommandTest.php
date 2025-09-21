<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\TestCase;
use Awssat\Visits\Tests\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class VisitsArchiveCommandTest extends TestCase
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

        $this->redis = Redis::connection('laravel-visits');

        if (count($keys = $this->redis->keys($this->app['config']['visits.keys_prefix'] . ':testing:*'))) {
            $this->redis->del($keys);
        }

    }

    /** @test */
    public function it_archives_daily_visits()
    {
        Carbon::setTestNow(Carbon::create(2023, 1, 1));

        $post = Post::create(['id' => 1]);
        visits($post)->increment();

        $this->artisan('visits:archive')->assertExitCode(0);

        $this->assertDatabaseHas('visits_archive', [
            'visitable_type' => 'posts',
            'visitable_id' => 1,
            'tag' => 'visits',
            'date' => '2023-01-01',
            'count' => 1,
        ]);
    }
}
