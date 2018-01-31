<?php

namespace Nh\Repositories\CustomerCgroups;

use Nh\Repositories\Entity;

class CustomerCgroup extends Entity
{
	protected $table = 'customer_cgroups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['customer_id', 'cgroup_id'];
}
