<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Tags\Tag;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class TagTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        // 'roles'
    ];

     public function transform(Tag $tag = null)
    {
        if (is_null($tag)) {
            return [];
        }

        $data = [
            'id'             => $tag->id,
            'name'           => $tag->name,
            'slug'           => $tag->slug,
            'color'          => $tag->color,
            'created_at'     => $tag->created_at ? $tag->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'     => $tag->updated_at ? $tag->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    // public function includeRoles(User $user = null)
    // {
    //     if (is_null($user)) {
    //         return $this->null();
    //     }

    //     return $this->collection($user->roles, new RoleTransformer());
    // }
}
