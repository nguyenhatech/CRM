<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;
use Illuminate\Support\Facades\DB;
use Nh\Repositories\Tags\TagRepository;
use Nh\Http\Transformers\TagTransformer;

class TagController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $tag;

    protected $validationRules = [];
    protected $validationMessages = [];

    public function __construct(TagRepository $tag, TagTransformer $transformer)
    {
        $this->tag = $tag;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->tag;
    }
}
