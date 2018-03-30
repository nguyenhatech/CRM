<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class LineCallServiceProvider extends ServiceProvider
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
            \Nh\Repositories\LineCalls\LineCallRepository::class,
            \Nh\Repositories\LineCalls\DbLineCallRepository::class
        );
    }
}
