<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CgroupAttributeServiceProvider extends ServiceProvider
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
            \Nh\Repositories\CgroupAttributes\CgroupAttributeRepository::class,
            \Nh\Repositories\CgroupAttributes\DbCgroupAttributeRepository::class
        );
    }
}
