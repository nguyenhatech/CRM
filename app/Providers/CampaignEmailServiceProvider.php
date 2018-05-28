<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignEmailServiceProvider extends ServiceProvider
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
            \Nh\Repositories\CampaignEmails\CampaignEmailRepository::class,
            \Nh\Repositories\CampaignEmails\DbCampaignEmailRepository::class
        );
    }
}
