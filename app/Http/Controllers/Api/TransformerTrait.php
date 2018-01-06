<?php

namespace Nh\Http\Controllers\Api;

use League\Fractal\TransformerAbstract;
use Nh\Http\Transformers\OptimusPrime;

trait TransformerTrait {

    protected $transformer;

    protected function setTransformer(TransformerAbstract $transformer)
    {
        $this->transformer = $transformer;
    }

    protected function getTransformer()
    {
        return $this->transformer;
    }

    protected function transform($entity)
    {
        $optimus = \App::make(OptimusPrime::class);
        return $optimus->transform($entity, $this->getTransformer());
    }
}
