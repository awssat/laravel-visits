<?php

namespace if4lcon\Bareq;

use Illuminate\Support\ServiceProvider;

class BareqServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/bareq.php' => config_path('bareq.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/bareq.php', 'bareq'
        );
    }
}
