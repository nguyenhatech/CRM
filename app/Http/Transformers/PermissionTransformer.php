<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Permissions\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'roles',
    ];

    public function transform(Permission $permission = null)
    {
        return (is_null($permission))? [
            'id'=> $permission->id, 
            'name'=> $permission->name, 
            'display_name' => $permission->display_name, 
            'description'  => $permission->description, 
            'created_at'   => optional($permission->created_at)->format('d-m-Y H:i:s'), 
            'updated_at'   => optional($permission->updated_at)->format('d-m-Y H:i:s')]: [];
    }

    public function includeRoles(Permission $permission = null)
    {
        return ((is_null($permission))) ? $this->collection($permission->roles, new RoleTransformer) : $this->null();
    }
}
