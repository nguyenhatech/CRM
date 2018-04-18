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
    public $fillable = ['type', 'name', 'description', 'color_code'];

    const WORK_TAG = 1;
    const LEAD_TAG = 2;

    const TYPE_LIST = [
    	self::WORK_TAG => 'Công việc',
    	self::LEAD_TAG => 'Đối tượng'
    ];
}
