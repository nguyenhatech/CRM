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
        }

        return response()->json($result, 200);
    }

}
