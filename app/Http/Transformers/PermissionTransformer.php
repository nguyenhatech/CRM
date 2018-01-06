<?php

namespace Nh\Http\Transformers;

use Nh\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'roles',
    ];

    public function transform(Permission $permission = null)
    {
        if (is_null($permission)) return [];

        return [
            'id'           => $permission->id,
            'name'         => $permission->name,
            'display_name' => $permission->display_name,
            'description'  => $permission->description,
            'created_at'   => $permission->created_at->format('d-m-Y H:i:s'),
            'updated_at'   => $permission->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    public function includeRoles(Permission $permission = null)
    {
        if (is_null($permission)) return $this->null();
        return $this->collection($permission->roles, new RoleTransformer);
    }
}
