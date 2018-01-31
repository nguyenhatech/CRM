<?php

namespace Nh\Repositories\Campaigns;

use Nh\Repositories\Entity;

class Campaign extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id','template_id','cgroup_id','uuid','name','description','start_date','end_date','status'];

    public $dates = ['start_date', 'end_date'];

    const PEDDING = 0;
    const RUNING = 1;

    const LIST_STATUS = [
        self::PEDDING => 'Ngừng chạy',
        self::RUNING => 'Đang chạy'
    ];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

}
