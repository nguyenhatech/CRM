<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignSmsIncomingServiceProvider extends ServiceProvider
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
            \Nh\Repositories\CampaignSmsIncomings\CampaignSmsIncomingRepository::class,
            \Nh\Repositories\CampaignSmsIncomings\DbCampaignSmsIncomingRepository::class
        );
    }
}
