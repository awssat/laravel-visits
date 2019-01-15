<?php

namespace awssat\Visits;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/config/visits.php' => config_path('visits.php'),
        ], 'config');

        Carbon::macro('endOfxHours', function ($xhours) {

            if($xhours > 12) {
                throw new \Exception('12 is the maximum period in xHours feature');
            }

            $hour = collect(range(1, 24 / $xhours))
                ->map(function ($hour) use ($xhours) {
                    return $hour * $xhours;
                })->first(function ($hour) {
                    return $hour >= $this->hour;
                });

            return $this->setTime($hour , 59, 59);
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
            __DIR__.'/config/visits.php', 'visits'
        );
    }
}
