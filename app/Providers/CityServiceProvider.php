<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \Nh\Repositories\Cities\CityRepository::class,
            \Nh\Repositories\Cities\DbCityRepository::class
        );
    }
}
