<?php

namespace Nh\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Nh\Events\UpdateLevelCustomer::class => [
            \Nh\Listeners\UpdateLevelCustomerListener::class,
        ],
        \Nh\Events\PaymentSuccess::class => [
            \Nh\Listeners\UpdateLastPaymentListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
