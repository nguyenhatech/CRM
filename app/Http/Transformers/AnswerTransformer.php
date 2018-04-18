<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Answers\Answer;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class AnswerTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

    public function transform(Answer $answer = null)
    {
        if (is_null($answer)) {
            return [];
        }

        $data = [
            'id'               => $answer->id,
            'question_id'      => $answer->question_id,
            'question_content' => $answer->question ? $answer->question->content : '',
            'type'             => $answer->type,
            'type_txt'         => $answer->getTypeAnswerText(),
            'content'          => $answer->content,
            'status'           => $answer->status,
            'status_txt'       => $answer->getStatusText(),
            'created_at'       => $answer->created_at ? $answer->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }
}
