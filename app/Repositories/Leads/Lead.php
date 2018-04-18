<?php

namespace Nh\Repositories\Leads;

use Nh\Repositories\Entity;

class Lead extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'name', 'dob', 'gender', 'owner_id', 'customer_id', 'phone', 'email', 'address', 'city_id', 'ip', 'facebook', 'quality', 'source', 'utm_source', 'status'];

    // Danh sách nguồn lead
    const WEB_SOURCE   = 1;
    const PHONE_SOURCE = 2;
    const OTHER_SOURCE = 3;

    // Danh sách giới tính
    const MALE   = 1;
    const FEMALE = 2;

    // Danh sánh trạng thái
    const NEW_STATUS  = 1;
    const IN_PROGRESS = 2;
    const COMPLETED    = 3;

    const SOURCE_LIST = [
        0                  => '---',
        self::WEB_SOURCE   => 'Website',
        self::PHONE_SOURCE => 'Gọi điện thoại',
        self::OTHER_SOURCE => 'Nguồn khác'
    ];

    const GENDER_LIST = [
        0            => '---',
        self::MALE   => 'Nam',
        self::FEMALE => 'Nữ'
    ];

    const STATUS_LIST = [
        0                  => 'Không xác định',
        self::NEW_STATUS   => 'Mới',
        self::IN_PROGRESS  => 'Đang tiếp cận',
        self::COMPLETED    => 'Đã chốt'
    ];

    const QUALITY_LIST = [
        0   => 'Xấu',
        1   => 'Khá',
        2   => 'Tốt',
        3   => 'Rất tốt'
    ];

    protected static function boot() {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });
        parent::boot();
    }

    public function tickets()
    {
    	return $this->morphToMany('Nh\Repositories\Tickets\Ticket', 'ticketable');
    }

    public function comments()
    {
        return $this->morphMany('Nh\Repositories\Comments\Comment', 'commentable');
    }
}
