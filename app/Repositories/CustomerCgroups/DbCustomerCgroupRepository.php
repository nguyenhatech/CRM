<?php

namespace Nh\Repositories\CustomerCgroups;
use Nh\Repositories\BaseRepository;

class DbCustomerCgroupRepository extends BaseRepository implements CustomerCgroupRepository
{
    public function __construct(CustomerCgroup $customerCgroup)
    {
        $this->model = $customerCgroup;
    }

}
