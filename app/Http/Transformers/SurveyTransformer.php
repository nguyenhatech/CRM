<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Surveys\Survey;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class SurveyTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'questions'
    ];

    public function transform(Survey $survey = null)
    {
        if (is_null($survey)) {
            return [];
        }

        $data = [
            'id'         => $survey->id,
            'title'      => $survey->title,
            'status'     => $survey->status,
            'status_txt' => $survey->getStatusText(),
            'created_at' => $survey->created_at ? $survey->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function includeQuestions(Survey $survey = null)
    {   
        if (is_null($survey)) return $this->null();
        return $this->collection($survey->questions, new QuestionTransformer);
    }
}
