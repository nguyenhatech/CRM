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
            case 'customer-jobs':
                $jobs = [];
                foreach (\Nh\Repositories\Customers\Customer::JOBS as $key => $value) {
                    array_push($jobs, ['value' => $key, 'text' => $value]);
                }
                $result = [
                    'jobs' => $jobs,
                ];
                break;
            case 'target-campaigns':
                $targets = [];
                foreach (\Nh\Repositories\Campaigns\Campaign::TARGET_TYPE as $key => $value) {
                    array_push($targets, ['value' => $key, 'text' => $value]);
                }
                $result = [
                    'targets' => $targets,
                ];
                break;
            case 'target-type-promotions':
                $targets = [];
                foreach (\Nh\Repositories\Promotions\Promotion::LIST_TARGET_TYPE as $key => $value) {
                    array_push($targets, ['value' => $key, 'text' => $value]);
                }
                $result = [
                    'targets' => $targets,
                ];
                break;
            case 'call-type-lists':
                $list = [];
                foreach (\Nh\Repositories\PhoneCallHistories\PhoneCallHistory::CALL_TYPE_LIST as $key => $value) {
                    array_push($list, ['value' => $key, 'text' => $value]);
                }
                $result = [
                    'data' => $list,
                ];
                break;
            case 'call-status-lists':
                $list = [];
                foreach (\Nh\Repositories\PhoneCallHistories\PhoneCallHistory::STATUS_CALL_LIST as $key => $value) {
                    array_push($list, ['value' => $key, 'text' => $value]);
                }
                $result = [
                    'data' => $list,
                ];
                break;
        }

        return response()->json($result, 200);
    }

}
