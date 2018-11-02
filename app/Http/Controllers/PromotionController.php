<?php

namespace Nh\Http\Controllers;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\ResponseHandler;
use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Http\Transformers\PromotionTransformer;
use Nh\Http\Controllers\Api\TransformerTrait;

class PromotionController extends Controller
{
    use ResponseHandler, TransformerTrait;
    public $promotion;

    public function __construct(PromotionRepository $promotion, PromotionTransformer $transformer)
    {
        $this->promotion = $promotion;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->promotion;
    }

    public function index(Request $request)
    {
        $params     = $request->all();
        $pageSize   = $request->get('limit', 25);
        $sort       = explode(':', $request->get('sort', 'id:1'));

        $datas = $this->getResource()->getAllPromotions($params, $pageSize, $sort);
        
        return $this->successResponse($datas);
    }
}
