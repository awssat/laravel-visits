<?php

namespace if4lcon\Bareq\Tests\Feature;

use Carbon\Carbon;
use if4lcon\Bareq\Tests\Post;
use Illuminate\Support\Facades\Redis;
use if4lcon\Bareq\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VisitsTest extends TestCase
{
    use RefreshDatabase;

    protected static $runSetup = false;

    public function setUp()
    {
        parent::setUp();

        if (static::$runSetup) {

            $this->app['config']['database.redis.laravel-visits'] = [
                'host' => env('REDIS_HOST', 'localhost'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => 0,
                'read_timeout' => 60,
            ];

            $cc = Redis::keys('bareq:testing:*');

            if (count($cc)) {
                Redis::del($cc);
            }
        }
    }

    /** @test * */
    public function config_test_laravel_visits()
    {
        $this->app['config']['database.redis.laravel-visits'] = [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
            'read_timeout' => 60,
        ];

        $this->assertEquals('laravel-visits', visits(Post::create()->fresh())->connection());
    }

    /** @test * */
    public function config_test_default()
    {
        $this->assertEmpty(visits(Post::create()->fresh())->connection());

        static::$runSetup = true;
    }

    /** @test */
    public function multi_tags_storing()
    {
        $userA = Post::create()->fresh();

        visits($userA)->increment();

        visits($userA, 'clicks')->increment();
        visits($userA, 'clicks2')->increment();

        $keys = Redis::keys('bareq:testing:*');

        $this->assertContains('bareq:testing:posts_visits', $keys);
        $this->assertContains('bareq:testing:posts_clicks', $keys);
        $this->assertContains('bareq:testing:posts_clicks2', $keys);
    }

    /** @test */
    public function multi_tags_visits()
    {
        $userA = Post::create()->fresh();

        visits($userA)->increment();

        visits($userA, 'clicks')->increment();

        $this->assertEquals([1, 1,], [ visits($userA)->count(), visits($userA, 'clicks')->count() ]);
    }

    /** @test */
    public function periods_test()
    {
        //somone add something on end of the week
        Carbon::setTestNow(Carbon::now()->endOfWeek());

        $userA = Post::create()->fresh();

        visits($userA)->increment();

        //it should be there fo breif of time
        $this->assertEquals([1, 1, 1, 1, 1], [
            visits($userA)->count(),
            visits($userA)->period('day')->count(),
            visits($userA)->period('week')->count(),
            visits($userA)->period('month')->count(),
            visits($userA)->period('year')->count()
        ]);

        //after seconds it should be empty for week and day
        sleep(1);
        $this->assertEquals([1, 0, 0, 1, 1], [
            visits($userA)->count(),
            visits($userA)->period('day')->count(),
            visits($userA)->period('week')->count(),
            visits($userA)->period('month')->count(),
            visits($userA)->period('year')->count()
        ]);

        //he came after a 5 minute later
        Carbon::setTestNow(Carbon::now()->endOfWeek()->addHours(1));

        sleep(1);
        visits($userA)->forceIncrement();

        $this->assertEquals([2, 1, 1, 2, 2], [
            visits($userA)->count(),
            visits($userA)->period('day')->count(),
            visits($userA)->period('week')->count(),
            visits($userA)->period('month')->count(),
            visits($userA)->period('year')->count()
        ]);
    }


    /** @test */
    public function referer_test()
    {
        $this->referer->put('google.com');

        $Post = Post::create()->fresh();

        visits($Post)->forceIncrement();

        $this->referer->put('twitter.com');

        visits($Post)->forceIncrement(10);

        $this->assertEquals(['twitter.com' => 10, 'google.com' => 1,], visits($Post)->refs());
    }

    /** @test */
    public function store_country_aswell()
    {
        $Post = Post::create()->fresh();

        visits($Post)->increment(1, true, true, true, '88.17.102.155');

        $this->assertEquals(1, visits($Post)->country('es')->count());
    }

    /** @test */
    public function get_countries()
    {
        $Post = Post::create()->fresh();

        $ips = [
            '88.17.102.155',
            '178.80.134.112',
            '83.96.36.50',
            '211.202.2.111',
        ];

        $x = 1;
        foreach ($ips as $ip)
        {
            visits($Post)->increment($x++, true, true, true, $ip);
        }

        visits($Post)->increment(20, true, true, true, '178.80.134.112');

        $this->assertEquals(['sa' => 22, 'kr' => 4, 'kw' => 3, 'es' => 1], visits($Post)->countries(-1));
    }

    /**
     * @test
     */
    public function it_reset_counter()
    {
        $post1 = Post::create()->fresh();
        $post2 = Post::create()->fresh();
        $post3 = Post::create()->fresh();

        visits($post1)->increment(10);

        visits($post2)->increment(5);

        visits($post3)->increment();

        visits($post1)->reset();

        $this->assertEquals(
            [2, 3],
            visits('if4lcon\Bareq\Tests\Post')->top(5)->pluck('id')->toArray()
        );

    }

    /** @test */
    public function reset_specific_ip()
    {
        $post = Post::create()->fresh();

        visits($post)->increment(10);

        //dd
        $ips = [
            '125.0.0.2',
            '129.0.0.2',
            '124.0.0.2'
        ];
        $key = config('bareq.redis_keys_prefix') . ":testing:recorded_ips:Post_1:";

        foreach ($ips as $ip) {
            Redis::set( $key . $ip, true, 'EX', 15 * 60, 'NX');
        }

        visits($post)->increment(10);

        $this->assertEquals(
            10,
            visits($post)->count()
        );

        visits($post)->reset('ips', '127.0.0.1');


        $ips_in_redis = collect(Redis::keys(config('bareq.redis_keys_prefix') . ":testing:recorded_ips:*"))->map(function ($ip) use ($key) {
            return str_replace($key, '', $ip);
        });

        $this->assertArrayNotHasKey(
            '127.0.0.1',
            $ips_in_redis->toArray()
        );

        visits($post)->increment(10);

        $this->assertEquals(
            20,
            visits($post)->count()
        );

    }

    /** @test */
    public function it_shows_proper_tops_and_lows()
    {
        $arr = [];
        $unique = [];

        //increase
        foreach (range(1, 20) as $id) {

            $post = Post::create()->fresh();

            while($inc = rand(1, 200)) {
                if(!in_array($inc, $unique)) {
                    $unique[] = $inc;
                    break;
                }
            }

            visits($post)->period('day')->forceIncrement($inc, false);
            visits($post)->forceIncrement($inc, false);

            $arr[$id] = visits($post)->period('day')->count();
        }

        $this->assertEquals(
            collect($arr)->sort()->reverse()->keys()->take(10)->toArray(),
            visits('if4lcon\Bareq\Tests\Post')->period('day')->top(10)->pluck('id')->toArray()
        );

        $this->assertEquals(
            collect($arr)->sort()->keys()->take(10)->toArray(),
            visits('if4lcon\Bareq\Tests\Post')->period('day')->low(11)->pluck('id')->toArray()
        );


        visits('if4lcon\Bareq\Tests\Post')->period('day')->reset();

        $this->assertEquals(0,
            visits('if4lcon\Bareq\Tests\Post')->period('day')->count()
        );

        $this->assertEmpty(
            visits('if4lcon\Bareq\Tests\Post')->period('day')->top(10)
        );

        $this->assertNotEmpty(
            visits('if4lcon\Bareq\Tests\Post')->top(10)
        );

        $this->assertEquals(
            collect($arr)->sum(),
            visits('if4lcon\Bareq\Tests\Post')->count()
        );

    }

    /** @test */
    public function it_reset_ips()
    {
        $post1 = Post::create()->fresh();
        $post2 = Post::create()->fresh();

        visits($post1)->increment();

        visits($post2)->increment();

        visits($post1)->reset('ips');

        visits($post1)->increment();

        $this->assertEquals(2, visits($post1)->count());

        visits($post2)->increment();

        $this->assertEquals(1, visits($post2)->count());
    }

    /**
     * @test
     */
    public function it_counts_visits()
    {


        $post = Post::create()->fresh();

        $this->assertEquals(0,
            visits($post)->count()
        );

        visits($post)->increment();

        $this->assertEquals(1,
            visits($post)->count()
        );

        visits($post)->forceDecrement();

        $this->assertEquals(0,
            visits($post)->count()
        );

    }


    /**
     * @test
     */
    public function it_only_record_ip_for_amount_of_time()
    {
        $post = Post::create()->fresh();

        visits($post)->seconds(1)->increment();

        sleep(visits($post)->timeLeft()->diffInSeconds() + 1);

        visits($post)->increment();

        $this->assertEquals(2, visits($post)->count());
    }



    /**
     * @test
     */
    public function it_list_from_cache()
    {
        $post1 = Post::create(['id' => 1, 'name' => '1'])->fresh();
        $post2 = Post::create(['id' => 2, 'name' => '2'])->fresh();
        $post3 = Post::create(['id' => 3, 'name' => '3'])->fresh();
        $post4 = Post::create(['id' => 4, 'name' => '4'])->fresh();
        $post5 = Post::create(['id' => 5, 'name' => '5'])->fresh();

        visits($post5)->forceIncrement(5);
        visits($post1)->forceIncrement(4);
        visits($post2)->forceIncrement(3);
        visits($post3)->forceIncrement(2);
        visits($post4)->forceIncrement(1);

        $fresh = visits('if4lcon\Bareq\Tests\Post')->top()->pluck('name');

        $post5->update(['name' => 'changed']);

        $cached = visits('if4lcon\Bareq\Tests\Post')->top()->pluck('name');

        $this->assertEquals($fresh->first(), $cached->first());

        $fresh2 = visits('if4lcon\Bareq\Tests\Post')
            ->fresh()
            ->top()
            ->pluck('name');

        $this->assertNotEquals($fresh2->first(), $cached->first());
    }
}
