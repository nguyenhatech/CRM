<?php

namespace Nh\Repositories\CgroupAttributes;

use Nh\Repositories\Entity;

class CgroupAttribute extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['cgroup_id', 'attribute', 'operation', 'value'];
}
