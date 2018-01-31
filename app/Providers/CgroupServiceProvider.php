<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CgroupServiceProvider extends ServiceProvider
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
            \Nh\Repositories\Cgroups\CgroupRepository::class,
            \Nh\Repositories\Cgroups\DbCgroupRepository::class
        );
    }
}
