<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignSmsServiceProvider extends ServiceProvider
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
            \Nh\Repositories\CampaignSms\CampaignSmsRepository::class,
            \Nh\Repositories\CampaignSms\DbCampaignSmsRepository::class
        );
    }
}
