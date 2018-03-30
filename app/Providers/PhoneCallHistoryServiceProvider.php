<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class PhoneCallHistoryServiceProvider extends ServiceProvider
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
            \Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository::class,
            \Nh\Repositories\PhoneCallHistories\DbPhoneCallHistoryRepository::class
        );
    }
}
