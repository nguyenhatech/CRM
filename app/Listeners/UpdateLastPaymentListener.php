<?php

namespace Nh\Listeners;

use Nh\Events\PaymentSuccess;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLastPaymentListener implements ShouldQueue
{
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
     * @param  =PaymentSuccess  $event
     * @return void
     */
    public function handle(PaymentSuccess $event)
    {
        $customer = $event->paymentHistory->customer;
        $customer->last_payment = \Carbon\Carbon::now();
        $customer->save();
    }
}
