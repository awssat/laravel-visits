<?php

namespace Awssat\Visits\Tests\Feature;

use Awssat\Visits\Tests\Post;
use Awssat\Visits\Models\Visit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Awssat\Visits\VisitsServiceProvider;
use Awssat\Visits\Relations\VisitsHasOne;

class RelationExistenceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('visits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('primary_key');
            $table->string('secondary_key')->nullable();
            $table->unsignedInteger('score');
            $table->json('list')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            $table->unique(['primary_key', 'secondary_key']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function tearDown(): void
    {
        Schema::dropIfExists('visits');
        Schema::dropIfExists('posts');
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [VisitsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('visits.engine', \Awssat\Visits\DataEngines\EloquentEngine::class);
    }

    public function test_with_count_sql_generation_sqlite()
    {
        Post::resolveRelationUsing('visit', function ($model) {
            return visits($model)->relation();
        });

        $sql = Post::withCount('visit')->toSql();

        $this->assertStringContainsString('"posts"."id" = "visits"."secondary_key"', $sql);
        $this->assertStringNotContainsString('CAST(', $sql);
    }

    public function test_with_count_sql_generation_pgsql()
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('getAttribute')->willReturn('13.0');

        $mockConnection = new \Illuminate\Database\PostgresConnection($pdo, 'test_db', '', ['driver' => 'pgsql']);

        $parentQuery = new \Illuminate\Database\Eloquent\Builder(
            new \Illuminate\Database\Query\Builder($mockConnection, $mockConnection->getQueryGrammar(), $mockConnection->getPostProcessor())
        );
        $parentQuery->setModel(new Post);

        $childQuery = new \Illuminate\Database\Eloquent\Builder(
            new \Illuminate\Database\Query\Builder($mockConnection, $mockConnection->getQueryGrammar(), $mockConnection->getPostProcessor())
        );
        $childQuery->setModel(new Visit);

        $relation = new VisitsHasOne($childQuery, new Post, 'visits.secondary_key', 'id');

        $existenceQuery = $relation->getRelationExistenceQuery($childQuery, $parentQuery, ['*']);
        $sql = $existenceQuery->toSql();

        $this->assertStringContainsString('CAST("posts"."id" AS VARCHAR)', $sql);
    }
}
