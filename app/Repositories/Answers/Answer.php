<?php

namespace Nh\Repositories\Answers;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['question_id', 'type', 'content', 'status'];

    protected $dates = ['deleted_at'];

    const ENABLE = 1;
    const DISABLE = 0;

    // Danh sách trạng thái
    const LIST_STATUS = [
        self::ENABLE => 'Kích hoạt',
        self::DISABLE => 'Không khích hoạt'
    ];

    const LIKE = 1;
    const UNLIKE = 0;
    // Danh sách loại câu trả lời
    const LIST_TYPE_ANSWER = [
        self::LIKE => 'Điều khách thích về chuyến đi',
        self::UNLIKE => 'Điều khách không thích về chuyến đi'
    ];

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getTypeAnswerText()
    {
        return array_key_exists($this->type, self::LIST_TYPE_ANSWER) ? self::LIST_TYPE_ANSWER[$this->status] : 'Không xác định';
    }
}
