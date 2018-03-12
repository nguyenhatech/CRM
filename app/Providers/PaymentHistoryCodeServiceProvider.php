<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentHistoryCodeServiceProvider extends ServiceProvider
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
            \Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCodeRepository::class,
            \Nh\Repositories\PaymentHistoryCodes\DbPaymentHistoryCodeRepository::class
        );
    }
}
