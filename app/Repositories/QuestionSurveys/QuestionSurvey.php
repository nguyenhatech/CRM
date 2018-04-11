<?php

namespace Nh\Repositories\QuestionSurveys;

use Nh\Repositories\Entity;

class QuestionSurvey extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['survey_id', 'question_id'];
    
}
