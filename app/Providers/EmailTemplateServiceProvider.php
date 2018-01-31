<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class EmailTemplateServiceProvider extends ServiceProvider
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
            \Nh\Repositories\EmailTemplates\EmailTemplateRepository::class,
            \Nh\Repositories\EmailTemplates\DbEmailTemplateRepository::class
        );
    }
}
