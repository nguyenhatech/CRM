<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Feedback\Feedback;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class FeedbackTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

    public function transform(Feedback $feedback = null)
    {
        if (is_null($feedback)) {
            return [];
        }

        $data = [
            'id'          => $feedback->id,
            'customer_id' => $feedback->customer_id,
            'survey_id'   => $feedback->survey_id,
            'type'        => $feedback->type,
            'type_txt'    => $feedback->getTypeText(),
            'title'       => $feedback->title,
            'note'        => $feedback->note,
            'status'      => $feedback->status,
            'status_txt'  => $feedback->getStatusText(),
            'created_at'  => $feedback->created_at ? $feedback->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function includeLikes(Feedback $feedback = null)
    {   
        if (is_null($feedback)) return $this->null();
        return $this->collection($feedback->answersLike, new AnswerTransformer);
    }

    public function includeUnlikes(Feedback $feedback = null)
    {
        if (is_null($feedback)) return $this->null();
        return $this->collection($feedback->answersUnLike, new AnswerTransformer);
    }
}
