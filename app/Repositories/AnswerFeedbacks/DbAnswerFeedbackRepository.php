<?php

namespace Nh\Repositories\AnswerFeedbacks;
use Nh\Repositories\BaseRepository;

class DbAnswerFeedbackRepository extends BaseRepository implements AnswerFeedbackRepository
{
    public function __construct(AnswerFeedback $answerFeedback)
    {
        $this->model = $answerFeedback;
    }

}
