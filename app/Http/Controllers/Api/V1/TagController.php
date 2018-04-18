<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Tags\TagRepository;
use Nh\Http\Transformers\TagTransformer;

class TagController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $tag;

    public function __construct(TagRepository $tag, TagTransformer $transformer)
    {
    	$this->tag = $tag;
    	$this->setTransformer($transformer);
    	// $this->checkPermission('tag');
    }

    public function getResource()
    {
        return $this->tag;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, $sort);
        return $this->successResponse($models);
    }
}
