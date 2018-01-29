<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
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
            \Nh\Repositories\Webhooks\WebhookRepository::class,
            \Nh\Repositories\Webhooks\DbWebhookRepository::class
        );
    }
}
