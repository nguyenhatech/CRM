<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Feedback\Feedback;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class FeedbackTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'customer',
        'user',
        'survey',
        'answers'
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
            'user_id'     => $feedback->user_id,
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

    public function includeCustomer(Feedback $feedback = null)
    {   
        if (is_null($feedback)) return $this->null();
        return $this->item($feedback->customer, new CustomerTransformer);
    }

    public function includeUser(Feedback $feedback = null)
    {
        if (is_null($feedback)) return $this->null();
        return $this->item($feedback->user, new UserTransformer);
    }

    public function includeSurvey(Feedback $feedback = null)
    {   
        if (is_null($feedback)) return $this->null();
        return $this->item($feedback->survey, new SurveyTransformer);
    }

    public function includeAnswers(Feedback $feedback = null)
    {   
        if (is_null($feedback)) return $this->null();
        return $this->collection($feedback->answers, new AnswerTransformer);
    }

}
