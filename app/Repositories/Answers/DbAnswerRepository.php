<?php

namespace Nh\Repositories\Answers;
use Nh\Repositories\BaseRepository;

class DbAnswerRepository extends BaseRepository implements AnswerRepository
{
    public function __construct(Answer $answer)
    {
        $this->model = $answer;
    }

}
