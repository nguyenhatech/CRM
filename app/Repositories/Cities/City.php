<?php

namespace Nh\Repositories\Cities;

use Nh\Repositories\Entity;

class City extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['name', 'short_name', 'code', 'priority'];
}
