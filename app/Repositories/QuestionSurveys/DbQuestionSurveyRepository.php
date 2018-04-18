<?php

namespace Nh\Repositories\QuestionSurveys;
use Nh\Repositories\BaseRepository;

class DbQuestionSurveyRepository extends BaseRepository implements QuestionSurveyRepository
{
    public function __construct(QuestionSurvey $questionSurvey)
    {
        $this->model = $questionSurvey;
    }

}
