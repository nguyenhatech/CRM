<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Comments\Comment;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CommentTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
    ];

     public function transform(Comment $comment = null)
    {
        if (is_null($comment)) {
            return [];
        }

        $data = [
            'id'            => $comment->id,
            'content'       => $comment->content,
            'created_at'    => $comment->created_at ? $comment->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'    => $comment->updated_at ? $comment->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }
}
