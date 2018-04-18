<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class WorkTagServiceProvider extends ServiceProvider
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
            \Nh\Repositories\WorkTags\WorkTagRepository::class,
            \Nh\Repositories\WorkTags\DbWorkTagRepository::class
        );
    }
}
