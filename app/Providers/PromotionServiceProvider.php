<?php

namespace Nh\Providers;

use Illuminate\Support\ServiceProvider;

class PromotionServiceProvider extends ServiceProvider
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
            \Nh\Repositories\Promotions\PromotionRepository::class,
            \Nh\Repositories\Promotions\DbPromotionRepository::class
        );
    }
}
