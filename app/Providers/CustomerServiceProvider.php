<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
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
            \Nh\Repositories\Customers\CustomerRepository::class,
            \Nh\Repositories\Customers\DbCustomerRepository::class
        );
    }
}
