<?php

namespace Nh\Repositories\ClientCustomers;
use Nh\Repositories\BaseRepository;

class DbClientCustomerRepository extends BaseRepository implements ClientCustomerRepository
{
    public function __construct(ClientCustomer $clientCustomer)
    {
        $this->model = $clientCustomer;
    }
}
