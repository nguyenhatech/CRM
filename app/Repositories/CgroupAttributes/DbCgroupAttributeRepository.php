<?php

namespace Nh\Repositories\CgroupAttributes;
use Nh\Repositories\BaseRepository;

class DbCgroupAttributeRepository extends BaseRepository implements CgroupAttributeRepository
{
    public function __construct(CgroupAttribute $cgroupAttribute)
    {
        $this->model = $cgroupAttribute;
    }

}
