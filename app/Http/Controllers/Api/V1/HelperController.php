<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;

class HelperController extends ApiController
{
    use RestfulHandler;

    protected $userType;

    public function index(Request $request, $name, $option = null)
    {

        $result = [];

        switch ($name) {
            case 'promotions':
                $result = [
                    'list_type_promotions' => \Nh\Repositories\Promotions\Promotion::getListTypePromotions(),
                ];
                break;
            case 'customer-levels':
                $result = [
                    'levels' => \Nh\Repositories\Customers\Customer::getListLevel(),
                ];
                break;
            case 'period-campaigns':
                $result = [
                    'periods' => \Nh\Repositories\Campaigns\Campaign::getListPeriod(),
                ];
                break;
        }

        return response()->json($result, 200);
    }

}
