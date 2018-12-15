<?php

namespace Nh\Events;

use Illuminate\Queue\SerializesModels;
use Nh\Repositories\Customers\Customer;

class NewCustomer
{
    use SerializesModels;

    public $customer;

    /**
     * Create a new event instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
