<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Cities\CityRepository;
use Nh\Repositories\Cities\City;
use Nh\Http\Transformers\CityTransformer;

class CityController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $city;

    public function __construct(CityRepository $city, CityTransformer $transformer)
    {
        $this->city = $city;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->city;
    }

}
