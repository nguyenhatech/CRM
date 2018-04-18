<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Tags\Tag;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class TagTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
    ];

    public function transform(Tag $tag = null)
    {
        if (is_null($tag)) {
            return [];
        }

        $data = [
            'id'            => $tag->id,
            'type'          => $tag->type,
            'type_text'     => Tag::TYPE_LIST[$tag->type],
            'name'          => $tag->name,
            'description'   => $tag->description,
            'color_code'    => $tag->color_code,
            'created_at'    => $tag->created_at ? $tag->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'    => $tag->updated_at ? $tag->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }
}
