<?php

namespace Nh\Http\Transformers;

use Nh\Role;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'users',
        'permissions',
    ];

    public function transform(Role $role = null)
    {
        if (is_null($role)) return [];

        return [
            'id'           => $role->id,
            'name'         => $role->name,
            'display_name' => $role->display_name,
            'description'  => $role->description,
            'created_at'   => $role->created_at->format('d-m-Y H:i:s'),
            'updated_at'   => $role->updated_at->format('d-m-Y H:i:s'),
        ];
    }

    public function includeUsers(Role $role = null)
    {
        if (is_null($role)) return $this->null();
        return $this->collection($role->users, new UserTransformer);
    }

    public function includePermissions(Role $role = null)
    {
        if (is_null($role)) return $this->null();
        return $this->collection($role->perms, new PermissionTransformer);
    }
}
