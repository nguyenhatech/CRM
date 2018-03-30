<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository;
use Nh\Http\Transformers\PhoneCallHistoryTransformer;

class CallHistoryController extends ApiController
{
	use TransformerTrait, RestfulHandler;

    protected $phoneCall;

    public function __construct(PhoneCallHistoryRepository $phoneCall, PhoneCallHistoryTransformer $transformer)
    {
    	$this->phoneCall = $phoneCall;
    	$this->setTransformer($transformer);
    	$this->checkPermission('callhistory');
    }

    public function getResource()
    {
        return $this->phoneCall;
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $datas = $this->getResource()->getByQuery($params, $pageSize, $sort);

        return $this->successResponse($datas);
    }
}
