<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Cgroups\Cgroup;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CgroupTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'attributes',
        'customers'
    ];

     public function transform(Cgroup $cgroup = null)
    {
        if (is_null($cgroup)) {
            return [];
        }

        $data = [
            'id'              => $cgroup->uuid,
            'filter'          => $cgroup->filter,
            'name'            => $cgroup->name,
            'client_id'       => $cgroup->client_id,
            'description'     => $cgroup->description,
            'avatar'          => $cgroup->avatar,
            'avatar_path'     => $cgroup->getAvatar(),
            'created_at'      => $cgroup->created_at ? $cgroup->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'      => $cgroup->updated_at ? $cgroup->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeCustomers(Cgroup $cgroup = null)
    {
        if (is_null($cgroup)) {
            return $this->null();
        }

        return $this->collection($cgroup->customers, new CustomerTransformer());
    }

    public function includeAttributes(Cgroup $cgroup = null)
    {
        if (is_null($cgroup)) {
            return $this->null();
        }

        return $this->collection($cgroup->attributes, new CgroupAttributeTransformer());
    }
}
