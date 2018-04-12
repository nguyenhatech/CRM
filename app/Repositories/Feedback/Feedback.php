<?php

namespace Nh\Repositories\Feedback;

use Nh\Repositories\Entity;

class Feedback extends Entity
{

    protected $table = 'feedbacks';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['customer_id','survey_id','type','title','note','status'];

    const ENABLE = 1;
    const DISABLE = 0;

    // Danh sách trạng thái
    const LIST_STATUS = [
        self::ENABLE => 'Kích hoạt',
        self::DISABLE => 'Không khích hoạt'
    ];

    const CUSTOMER = 1;
    const CSKH = 2;

    // Danh sách trạng thái
    const LIST_TYPE_FEEDBACK = [
        self::CUSTOMER => 'Từ người dùng',
        self::CSKH => 'Từ nhân viên chăm sóc khách hàng'
    ];

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getTypeText()
    {
        return array_key_exists($this->type, self::LIST_TYPE_FEEDBACK) ? self::LIST_TYPE_FEEDBACK[$this->type] : 'Không xác định';
    }

    public function answers()
    {
        return $this->belongsToMany('Nh\Repositories\Answers\Answer', 'answer_feedback', 'feedback_id', 'answer_id');
    }

    public function customer()
    {
        return $this->belongsTo('Nh\Repositories\Answers\Answer')
    }

}
