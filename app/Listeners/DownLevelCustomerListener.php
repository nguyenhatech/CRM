<?php

namespace Nh\Listeners;

use Nh\Events\DownLevelCustomer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DownLevelCustomerListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DownLevel  $event
     * @return void
     */
    public function handle(DownLevelCustomer $event)
    {
        $customer = $event->paymentHistory->customer;
        $customer->last_payment = null;
        $customer->save();
    }
}
