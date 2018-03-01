<?php

namespace Nh\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nh\Repositories\Customers\Customer;

class UpdateLevelCustomer implements ShouldQueue
{
    use SerializesModels;
    public $customer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
