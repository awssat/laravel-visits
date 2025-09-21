<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\TestCase;
use Awssat\Visits\Tests\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Carbon;

class ArchiveDailyVisitsTest extends TestCase
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
    public function it_does_not_record_daily_visits_when_disabled()
    {
        $this->app['config']['visits.archive_daily_visits'] = false;

        $post = Post::create(['id' => 1]);
        visits($post)->increment();

        $this->assertCount(0, $this->redis->keys('*_day_daily_*'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_archive_command_is_run_while_disabled()
    {
        $this->app['config']['visits.archive_daily_visits'] = false;

        $this->artisan('visits:archive')
            ->expectsOutput('Daily visits archiving is disabled. Please enable it in config/visits.php')
            ->assertExitCode(0);
    }
}
