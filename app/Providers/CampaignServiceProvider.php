<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignServiceProvider extends ServiceProvider
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
            \Nh\Repositories\Campaigns\CampaignRepository::class,
            \Nh\Repositories\Campaigns\DbCampaignRepository::class
        );
    }
}
