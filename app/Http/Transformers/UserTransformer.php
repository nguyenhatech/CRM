<?php

namespace Nh\Http\Transformers;

use Nh\User;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'roles'
    ];

     public function transform(User $user = null)
    {
        if (is_null($user)) {
            return [];
        }

        $data = [
            'id'             => $user->uuid,
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'avatar'         => $user->avatar,
            'avatar_path'    => $user->getAvatar(),
            'is_active'      => $user->isActive(),
            'status'         => $user->status,
            'status_txt'     => $user->getStatusText(),
            'created_at'     => $user->created_at ? $user->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'     => $user->updated_at ? $user->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeRoles(User $user = null)
    {
        if (is_null($user)) {
            return $this->null();
        }

        return $this->collection($user->roles, new RoleTransformer());
    }
}
