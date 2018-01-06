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

        return [
            'id' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email
        ];
    }

    public function includeRoles(User $user = null)
    {
        if (is_null($user)) {
            return $this->null();
        }

        return $this->collection($user->roles, new RoleTransformer());
    }
}
