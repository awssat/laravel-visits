<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    protected $table = 'posts';
    protected $guarded = [];

    public function visits()
    {
        return visits($this)->relation();
    }
}

class PostgresCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['visits.engine'] = \Awssat\Visits\DataEngines\EloquentEngine::class;
        include_once __DIR__.'/../../database/migrations/create_visits_table.php.stub';
        (new \CreateVisitsTable())->up();
    }

    public function test_it_generates_correct_query_types_for_postgres()
    {
        $post1 = TestPost::create(['name' => 'p1']);
        $post2 = TestPost::create(['name' => 'p2']);

        visits($post1)->increment();
        visits($post2)->increment();

        DB::enableQueryLog();

        $posts = TestPost::with('visits')->get();

        $log = DB::getQueryLog();

        // Find the query that loads visits
        $visitsQuery = null;
        foreach ($log as $query) {
            if (strpos($query['query'], 'select * from "visits"') !== false) {
                $visitsQuery = $query;
                break;
            }
        }

        // If not found, look for standard select
        if (!$visitsQuery) {
            foreach ($log as $query) {
                if (strpos($query['query'], 'from "visits"') !== false) {
                    $visitsQuery = $query;
                    break;
                }
            }
        }

        $this->assertNotNull($visitsQuery, 'Visits query not found');

        // Check bindings
        $bindings = $visitsQuery['bindings'];

        $hasIntegerBindings = false;
        $hasStringBindings = false;

        foreach ($bindings as $binding) {
            if (is_int($binding) && ($binding === $post1->id || $binding === $post2->id)) {
                $hasIntegerBindings = true;
            }
             if (is_string($binding) && ($binding === (string) $post1->id || $binding === (string) $post2->id)) {
                $hasStringBindings = true;
            }
        }

        $this->assertFalse($hasIntegerBindings, 'Bindings should not be integers');
        $this->assertTrue($hasStringBindings, 'Bindings should be strings');
    }
}
