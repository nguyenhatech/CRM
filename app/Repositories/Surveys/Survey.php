<?php

namespace Nh\Repositories\Surveys;

use Nh\Repositories\Entity;

class Survey extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['title', 'status'];

    const ENABLE = 1;
    const DISABLE = 0;

    // Danh sách trạng thái
    const LIST_STATUS = [
        self::ENABLE => 'Kích hoạt',
        self::DISABLE => 'Không khích hoạt'
    ];

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function questions()
    {
        return $this->belongsToMany('Nh\Repositories\Questions\Question', 'question_survey', 'survey_id', 'question_id');
    }
}
