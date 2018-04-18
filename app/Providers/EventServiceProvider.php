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
            \Nh\Listeners\UpdateLastPaymentListener::class,
            \Nh\Listeners\SendPaymentToMerchant::class
        ],
        \Nh\Events\DownLevelCustomer::class => [
            \Nh\Listeners\DownLevelCustomerListener::class
        ],
        \Nh\Events\InfoCustomer::class => [
            \Nh\Listeners\SendCustomerToMerchant::class
        ],
        \Nh\Events\InfoCallIn::class => [
            \Nh\Listeners\CreateNewLead::class
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
