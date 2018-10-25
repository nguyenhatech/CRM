<?php

namespace Nh\Repositories\Tags;

use Nh\Repositories\Entity;

class Tag extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'color'
    ];

    public function customers()
    {
        return $this->belongsToMany('Nh\Repositories\Customers\Customer');
    }
}
