<?php

namespace Nh\Repositories\ClientCustomers;

use Nh\Repositories\Entity;

class ClientCustomer extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id', 'customer_id'];
}
