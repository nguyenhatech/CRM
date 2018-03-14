<?php

namespace Nh\Listeners;

use Nh\Events\UpdateLevelCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLevelCustomerListener implements ShouldQueue
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
     * @param  Event  $event
     * @return void
     */
    public function handle(UpdateLevelCustomer $event)
    {
        $customer = $event->customer;
        if ($customer->getTotalPoint() >= list_level_point()[LEVEL_DIAMOND]) {
            $customer->level = LEVEL_DIAMOND;
        } elseif ($customer->getTotalPoint() >= list_level_point()[LEVEL_GOLD]) {
            $customer->level = LEVEL_GOLD;
        } elseif ($customer->getTotalPoint() >= list_level_point()[LEVEL_SILVER]) {
            $customer->level = LEVEL_SILVER;
        } else {
            $customer->level = LEVEL_NORMAL;
        }
        $customer->save();
    }
}
