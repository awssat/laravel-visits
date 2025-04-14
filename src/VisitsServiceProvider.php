<?php

namespace Awssat\Visits;

use Awssat\Visits\Commands\CleanCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Torann\GeoIP\GeoIPServiceProvider;

class VisitsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/visits.php' => config_path('visits.php'),
        ], 'config');

        if (! class_exists('CreateVisitsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_visits_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_visits_table.php'),
            ], 'migrations');
        }

        Carbon::macro('endOfxHours', function ($xhours) {
            if ($xhours > 12) {
                throw new \Exception('12 is the maximum period in xHours feature');
            }
            $h = $this->hour;

            return $this->setTime(
                ($h % $xhours == 0 ? 'min' : 'max')($h - ($h % $xhours), $h - ($h % $xhours) + $xhours),
                59,
                59
            );
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/visits.php',
            'visits'
        );

        // Register GeoIP service provider if not already registered
        if (!$this->app->providerIsLoaded(GeoIPServiceProvider::class)) {
            $this->app->register(GeoIPServiceProvider::class);
        }
        
        // Register GeoIP facade if not already registered
        $geoipAlias = $this->app->getAlias('GeoIP');
        if ($geoipAlias === null) {
            $this->app->alias('GeoIP', \Torann\GeoIP\Facades\GeoIP::class);
        }

        // For testing environments, use a mock implementation
        if ($this->app->environment('testing')) {
            $this->app->singleton('geoip', function () {
                return new class {
                    public function getLocation() {
                        return [
                            'ip' => '127.0.0.0',
                            'iso_code' => 'US',
                            'country' => 'United States',
                            'city' => 'New Haven',
                            'state' => 'CT',
                            'state_name' => 'Connecticut',
                            'postal_code' => '06510',
                            'lat' => 41.31,
                            'lon' => -72.92,
                            'timezone' => 'America/New_York',
                            'continent' => 'NA',
                            'default' => true,
                            'currency' => 'USD',
                        ];
                    }
                };
            });
        }

        $this->app->bind('command.visits:clean', CleanCommand::class);

        $this->commands([
            'command.visits:clean',
        ]);
    }
}
