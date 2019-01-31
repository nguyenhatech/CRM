<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;
use Validator, Throwable, Log;

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

        $this->registerCustomValidation();
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

    protected function registerCustomValidation()
    {
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/^[0-9]{9,19}$/', $value);
        });

        Validator::extend('website_address', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/^(http\:\/\/|https\:\/\/)?([a-z0-9][a-z0-9\-]*\.)+[a-z0-9][a-z0-9\-]*$/', $value);
        });

    }
}
