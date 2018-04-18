<?php

namespace Nh\Repositories\Questions;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['content', 'status'];

    protected $dates = ['deleted_at'];

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

    public function answers()
    {
        return $this->hasMany('Nh\Repositories\Answers\Answer');
    }

    public function answersLike()
    {
        return $this->hasMany('Nh\Repositories\Answers\Answer')->where('type', 1);
    }

    public function answersUnLike()
    {
        return $this->hasMany('Nh\Repositories\Answers\Answer')->where('type', 0);
    }
}
