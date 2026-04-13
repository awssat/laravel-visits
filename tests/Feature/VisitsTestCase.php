<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\Post;
use Awssat\Visits\Tests\User;
use Awssat\Visits\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;


abstract class VisitsTestCase extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }


    /**
     */
    public function test_it_reset_counter()
    {
        $post1 = Post::create()->fresh();
        $post2 = Post::create()->fresh();
        $post3 = Post::create()->fresh();

        visits($post1)->increment(10);
        visits($post1)->reset();

        visits($post2)->increment(5);
        visits($post3)->increment();


        $this->assertEquals(
            [2, 3],
            visits('Awssat\Visits\Tests\Post')->top(5)->pluck('id')->toArray()
        );
    }

    
    public function test_reset_specific_ip()
    {
        $post = Post::create()->fresh();

        visits($post)->increment(10);

        //dd
        $ips = [
            '125.0.0.2',
            '129.0.0.2',
            '124.0.0.2'
        ];
    
        $prefix = 'testing:posts_visits_';
        $key = $prefix.'recorded_ips:1:';

        foreach ($ips as $ip) {
            if(! $this->connection->exists($key.$ip)) {
                $this->connection->set($key.$ip, true);
            } else {
                $this->connection->setExpiration($key.$ip, 15*60);
            }
        }

        visits($post)->increment(10);

        $this->assertEquals(
            10,
            visits($post)->count()
        );

        visits($post)->reset('ips', '127.0.0.1');

        $ips_in_db = Collection::make($this->connection->search($prefix.'recorded_ips:*'))
                ->map(function ($ip){
                    return substr($ip, strrpos($ip, ':') + 1);
                });

        $this->assertNotContains(
            '127.0.0.1',
            $ips_in_db
        );

        visits($post)->increment(10);

        $this->assertEquals(
            20,
            visits($post)->count()
        );
    }

    
    public function test_it_shows_proper_tops_and_lows()
    {
        $arr = [];
        $unique = [];

        //increase
        foreach (range(1, 20) as $id) {
            $post = Post::create()->fresh();

            while ($inc = rand(1, 200)) {
                if (! in_array($inc, $unique)) {
                    $unique[] = $inc;
                    break;
                }
            }

            visits($post)->period('day')->forceIncrement($inc, ['periods']);
            visits($post)->forceIncrement($inc, ['periods']);

            $arr[$id] = visits($post)->period('day')->count();
        }

        $this->assertEquals(
            Collection::make($arr)->sort()->reverse()->keys()->take(10)->toArray(),
            visits('Awssat\Visits\Tests\Post')->period('day')->top(10)->pluck('id')->toArray()
        );

        $this->assertEquals(
            Collection::make($arr)->sort()->keys()->take(10)->toArray(),
            visits('Awssat\Visits\Tests\Post')->period('day')->low(11)->pluck('id')->toArray()
        );

        visits('Awssat\Visits\Tests\Post')->period('day')->reset();

        $this->assertEquals(
            0,
            visits('Awssat\Visits\Tests\Post')->period('day')->count()
        );
        // dd(visits('Awssat\Visits\Tests\Post')->period('day')->top(10));

        $this->assertEmpty(
            visits('Awssat\Visits\Tests\Post')->period('day')->top(10)
        );

        $this->assertNotEmpty(
            visits('Awssat\Visits\Tests\Post')->top(10)
        );

        $this->assertEquals(
            Collection::make($arr)->sum(),
            visits('Awssat\Visits\Tests\Post')->count()
        );
    }

    
    public function test_it_reset_ips()
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
     */
    public function test_it_counts_visits()
    {
        $post = Post::create()->fresh();

        $this->assertEquals(
            0,
            visits($post)->count()
        );

        visits($post)->increment();

        $this->assertEquals(
            1,
            visits($post)->count()
        );

        visits($post)->forceDecrement();

        $this->assertEquals(
            0,
            visits($post)->count()
        );
    }

    /**
     */
    public function test_it_only_record_ip_for_amount_of_time()
    {
        $post = Post::create()->fresh();

        visits($post)->seconds(1)->increment();

        Carbon::setTestNow(Carbon::now()->addSeconds(visits($post)->ipTimeLeft() + 1));
        sleep(1);//for redis


        visits($post)->increment();

        $this->assertEquals(2, visits($post)->count());
    }

    /**
     */
    public function n_minus_1_bug()
    {
        foreach (range(1, 6) as $i) {
            $post = Post::create(['name' => $i])->fresh();
            visits($post)->forceIncrement();
        }

        $list = visits('Awssat\Visits\Tests\Post')->top(5)->pluck('name');

        $this->assertEquals(5, $list->count());
    }

    /**
     */
    public function test_it_list_from_cache()
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

        $fresh = visits('Awssat\Visits\Tests\Post')->top()->pluck('name');

        $post5->update(['name' => 'changed']);

        $cached = visits('Awssat\Visits\Tests\Post')->top()->pluck('name');

        $this->assertEquals($fresh->first(), $cached->first());

        $fresh2 = visits('Awssat\Visits\Tests\Post')
            ->fresh()
            ->top()
            ->pluck('name');

        $this->assertNotEquals($fresh2->first(), $cached->first());
    }

    /**
     */
    public function test_it_list_filtered_by_constraints()
    {
        $posts =[];

        foreach (['naji', 'fadi', 'hanadi', 'maghi', 'lafi'] as $player) {
            $posts[$player] = Post::create(['name' => $player])->fresh();
            visits($posts[$player])->forceIncrement(rand(2, 109));
        }
 
        $this->assertNotEquals(visits('Awssat\Visits\Tests\Post')->top(5, ['name' => 'naji']), [$posts['naji']]);
    }
}
