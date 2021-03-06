<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentHistoryServiceProvider extends ServiceProvider
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
            \Nh\Repositories\PaymentHistories\PaymentHistoryRepository::class,
            \Nh\Repositories\PaymentHistories\DbPaymentHistoryRepository::class
        );
    }
}
