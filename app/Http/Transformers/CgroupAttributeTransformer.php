<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\CgroupAttributes\CgroupAttribute;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CgroupAttributeTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        // 'roles'
    ];

     public function transform(CgroupAttribute $attribute = null)
    {
        if (is_null($attribute)) {
            return [];
        }

        $data = [
            'id'              => $attribute->id,
            'cgroup_id'       => $attribute->cgroup_id,
            'attribute'       => $attribute->attribute,
            'operation'       => $attribute->operation,
            'value'           => $attribute->value,
            'created_at'      => $attribute->created_at ? $attribute->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'      => $attribute->updated_at ? $attribute->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }
}
