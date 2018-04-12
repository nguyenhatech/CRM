<?php

namespace Nh\Repositories\AnswerFeedbacks;

use Nh\Repositories\Entity;

class AnswerFeedback extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['feedback_id', 'answer_id'];
}
