<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CustomerCgroupServiceProvider extends ServiceProvider
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
            \Nh\Repositories\CustomerCgroups\CustomerCgroupRepository::class,
            \Nh\Repositories\CustomerCgroups\DbCustomerCgroupRepository::class
        );
    }
}
