<?php

namespace if4lcon\Bareq\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use if4lcon\Bareq\BareqServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Referer\Referer;
use Spatie\Referer\CaptureReferer;
use Spatie\Referer\RefererServiceProvider;
use Torann\GeoIP\GeoIPServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /** @var \Illuminate\Contracts\Session\Session */
    protected $session;
    /** @var \Spatie\Referer\Referer */
    protected $referer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('geoip', array_merge(require __DIR__ . '/../vendor/torann/geoip/config/geoip.php'));
        $this->app['router']->get('/')->middleware(CaptureReferer::class, function () {
            return response(null, 200);
        });
        $this->session = $this->app['session.store'];
        $this->referer = $this->app['referer'];

        $this->runTestMigrations();
    }


    protected function withConfig(array $config)
    {
        $this->app['config']->set($config);
        $this->app->forgetInstance(Referer::class);
        $this->referer = $this->app->make(Referer::class);
    }

    /**
     * Get package service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GeoIPServiceProvider::class,
            RefererServiceProvider::class,
            BareqServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'GeoIP' => \Torann\GeoIP\Facades\GeoIP::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }
    /**
     * Run migrations for tables used for testing purposes.
     *
     * @return void
     */
    private function runTestMigrations()
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();
        if (! $schema->hasTable('post')) {
            $schema->create('post', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }
}

class Post extends Model
{
    protected $guarded = [];
    protected $table = 'post';
}