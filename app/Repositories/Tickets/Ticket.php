<?php

namespace Nh\Repositories\Tickets;

use Nh\Repositories\Entity;

class Ticket extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['name', 'prioty', 'type', 'status', 'deadline', 'start_time', 'end_time', 'email_alert', 'notify_alert', 'description', 'created_by'];

    const HIGHEST_PRIOTY    = 1;
    const HIGH_PRIOTY       = 2;
    const NORMAL_PRIOTY     = 3;
    const LOW_PRIOTY        = 4;
    const LOWEST_PRIOTY     = 5;

    const PRIOTY_LIST = [
        self::HIGHEST_PRIOTY    => 'Cao nhất',
        self::HIGH_PRIOTY       => 'Cao',
        self::NORMAL_PRIOTY     => 'Bình thường',
        self::LOW_PRIOTY        => 'Thấp',
        self::LOWEST_PRIOTY     => 'Thấp nhất'
    ];

    const NEW_TICKET        = 1;
    const IN_PROCESS_TICKET = 2;
    const FINISH_TICKET     = 3;
    const DELAY_TICKET      = 0;

    const STATUS_LIST = [
        self::NEW_TICKET        => 'Mới',
        self::IN_PROCESS_TICKET => 'Đang làm',
        self::FINISH_TICKET     => 'Hoàn thành',
        self::DELAY_TICKET      => 'Hoãn',
    ];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    /**
     * Người tạo
     */
    public function user()
    {
        return $this->hasOne('Nh\User', 'id', 'created_by');
    }

    /**
     * Danh sách người làm
     */
    public function users()
    {
        return $this->belongsToMany('Nh\User', 'ticket_user', 'ticket_id', 'user_id', 'id');
    }

    /**
     * Danh sách khách hàng
     */
    public function customers()
    {
    	return $this->morphedByMany('Nh\Repositories\Customers\Customer', 'ticketable');
    }

    /**
     * Danh sách liên hệ
     */
    public function leads()
    {
    	return $this->morphedByMany('Nh\Repositories\Leads\Lead', 'ticketable');
    }
}
