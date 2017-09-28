<?php


/*
 *
 * visits('App\Post')->top(10) //tested
 * visits('App\Post')->low(10) //tested
 *
 * visits('App\Post')->fresh()->top(10) //tested
 *
 * visits('App\Post')->period('year')->top(10) //tested
 * visits('App\Post')->period('month')->low(10) //tested
 * visits('App\Post')->period('day')->count() //tested
 *
 * visits($post)->count() //tested
 * visits($post)->period('day')->count() //tested
 *
 * visits($post)->increment() //tested
 * visits($post)->seconds(30)->increment() //tested
 *
 * visits($post)->decrement(5) //tested
 * visits($post)->seconds(1)->decrement() //tested
 *
 * visits($post)->forceIncrement() //tested
 * visits($post)->forceDecrement() //tested
 *
 * visits('App\Post')->reset('factory') //tested
 * visits('App\Post')->reset('lists') //tested
 * visits('App\Post')->period('year')->reset() //tested
 * visits($post)->period('year')->reset() //tested
 * visits($post)->reset('ips'); //tested
 * visits($post)->reset('ips', '127.0.0.1'); //tested
 * visits($post)->reset(); //tested
 *
 */


namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class visitsTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        visits('App\Post')
            ->reset('factory');

    }

    function create($class, $times = null, $attributes = [])
    {
        return factory($class, $times)->create($attributes);
    }

    function make($class, $times = null, $attributes = [])
    {
        return factory($class, $times)->make($attributes);
    }

    /** @test */
    public function dont_reset_time_expiration_every_increment()
    {
        $post = $this->create('App\Post');

        visits($post)->forceIncrement();

        $timenow = visits($post)->timeLeft('day')->diffInSeconds();

        visits($post)->forceIncrement();

        $timethen = visits($post)->timeLeft('day')->diffInSeconds();

        $this->assertNotEquals($timenow, $timethen);
    }

    /**
     * @test
     */
    public function it_reset_counter()
    {
        $post1 = $this->create('App\Post');
        $post2 = $this->create('App\Post');
        $post3 = $this->create('App\Post');

        visits($post1)->increment(10);

        visits($post2)->increment(5);

        visits($post3)->increment();

        visits($post1)->reset();

        $this->assertEquals(
            [2, 3, 1],
            visits('App\Post')->top()->pluck('id')->toArray()
        );

    }

    /** @test */
    public function reset_specific_ip()
    {
        $post = $this->create('App\Post');

        visits($post)->increment(10);

        $ips = [
            '125.0.0.2',
            '129.0.0.2',
            '124.0.0.2'
        ];

        $key = config('bareq.redis_keys_prefix') . ":testing:recorded_ips:post_1:";

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

        $this->assertEquals(
            $ips_in_redis->toArray(),
            $ips
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
        $this->create('App\Post', 20);

        $arr = [];
        $unique = [];

        //increase
        foreach (range(1, 20) as $id) {
            $post = \App\Post::find($id);

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
            visits('App\Post')->period('day')->top(10)->pluck('id')->toArray()
        );

        $this->assertEquals(
            collect($arr)->sort()->keys()->take(10)->toArray(),
            visits('App\Post')->period('day')->low(10)->pluck('id')->toArray()
        );


        visits('App\Post')->period('day')->reset();

        $this->assertEquals(0,
            visits('App\Post')->period('day')->count()
        );

        $this->assertEmpty(
            visits('App\Post')->period('day')->top(10)
        );

        $this->assertNotEmpty(
            visits('App\Post')->top(10)
        );

        $this->assertEquals(
            collect($arr)->sum(),
            visits('App\Post')->count()
        );

    }

    /** @test */
    public function it_reset_ips()
    {
        $post1 = $this->create('App\Post');
        $post2 = $this->create('App\Post');

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


        $post = $this->create('App\Post');

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
    public function it_really_counts()
    {


        $post = $this->create('App\Post');

        visits($post)->forceIncrement(900000, false);
        visits($post)->period('week')->forceIncrement(20, false);
        visits($post)->period('month')->forceIncrement(400, false);
        visits($post)->period('year')->forceIncrement(5000, false);
        visits($post)->increment(50);

        $day      = visits($post)->period('day')->count();
        $week     = visits($post)->period('week')->count();
        $month    = visits($post)->period('month')->count();
        $year     = visits($post)->period('year')->count();
        $all_time = visits($post)->count();

        $this->assertEquals(
            [50, 70, 450, 5050, 900050],
            [$day, $week, $month, $year, $all_time]
        );

    }

    /**
     * @test
     */
    public function it_only_record_ip_for_amount_of_time()
    {
        $post = $this->create('App\Post');

        visits($post)->seconds(1)->increment();

        sleep(visits($post)->timeLeft()->diffInSeconds());

        visits($post)->increment();

        $this->assertEquals(2, visits($post)->count());
    }


    /**
     * @test
     */
    public function periods_time_correct()
    {


        $post = $this->create('App\Post');

        visits($post)->increment();

        $day   = visits('App\Post')->timeLeft('day')->diffInDays();
        $week  = visits('App\Post')->timeLeft('week')->diffInDays();
        $month = visits('App\Post')->timeLeft('month')->diffInDays();
        $year  = visits('App\Post')->timeLeft('year')->diffInDays();

        $this->assertEquals(
            [1, 7, 30, 365],
            [$day, $week, $month, $year]
        );
    }


    /**
     * @test
     */
    public function it_list_from_cache()
    {


        $post1 = $this->create('App\Post');
        $post2 = $this->create('App\Post');
        $post3 = $this->create('App\Post');
        $post4 = $this->create('App\Post');
        $post5 = $this->create('App\Post');

        visits($post5)->forceIncrement(5);
        visits($post1)->forceIncrement(4);
        visits($post2)->forceIncrement(3);
        visits($post3)->forceIncrement(2);
        visits($post4)->forceIncrement(1);

        $fresh = visits('App\Post')->top()->pluck('title');

        $post5->update(['title' => 'changed']);

        $cached = visits('App\Post')->top()->pluck('title');

        $this->assertEquals($fresh->first(), $cached->first());

        $fresh2 = visits('App\Post')
            ->fresh()
            ->top()
            ->pluck('title');

        $this->assertNotEquals($fresh2->first(), $cached->first());

    }


}
