<?php

namespace Nh\Repositories\Cities;
use Nh\Repositories\BaseRepository;

class DbCityRepository extends BaseRepository implements CityRepository
{
    public function __construct(City $city)
    {
        $this->model = $city;
    }

}
