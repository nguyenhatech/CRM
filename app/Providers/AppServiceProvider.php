<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        \Horizon::auth(function ($request) {
            return \Auth::user() && \Auth::user()->isSuperAdmin();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\KRepository\KRepositoryServiceProvider::class);
        if (!empty(config('kproviders'))) {
            foreach (config('kproviders') as $provider )
            {
                $this->app->register( $provider );
            }
        }
    }
}
