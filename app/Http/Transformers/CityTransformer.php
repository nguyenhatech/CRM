<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Cities\City;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CityTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

     public function transform(City $city = null)
    {
        if (is_null($city)) {
            return [];
        }

        $data = [
            'id'           => $city->id,
            'name'         => $city->name,
            'short_name'   => $city->short_name,
            'code'         => $city->code,
            'priority'     => $city->priority
        ];

        return $data;
    }
}
