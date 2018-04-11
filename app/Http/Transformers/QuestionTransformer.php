<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Questions\Question;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class QuestionTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'likes',
        'unlikes'
    ];

    public function transform(Question $question = null)
    {
        if (is_null($question)) {
            return [];
        }

        $data = [
            'id'         => $question->id,
            'content'    => $question->content,
            'status'     => $question->status,
            'status_txt' => $question->getStatusText(),
            'created_at' => $question->created_at ? $question->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function includeLikes(Question $question = null)
    {   
        if (is_null($question)) return $this->null();
        return $this->collection($question->answersLike, new AnswerTransformer);
    }

    public function includeUnlikes(Question $question = null)
    {
        if (is_null($question)) return $this->null();
        return $this->collection($question->answersUnLike, new AnswerTransformer);
    }
}
