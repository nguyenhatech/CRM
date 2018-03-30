<?php

namespace Nh\Repositories\PhoneCallHistories;

use Nh\Repositories\Entity;

class PhoneCallHistory extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['user_id', 'agent_id', 'transaction_id', 'from', 'to', 'hotline', 'type', 'call_type', 'status', 'start_time', 'end_time', 'stop_by'];

    const MOBILE_CALL 		= 1;
    const VOICE_DEVICE_CALL = 2;

    const CALL_DEVICE_TYPE_LIST = [
    	self::MOBILE_CALL       => 'mobile',
    	self::VOICE_DEVICE_CALL => 'voice_device'
    ];

    const CONNECTED_CALL = 1;
    const MISS_CALL      = 2;
    const BUSY_CALL      = 3;

    const STATUS_CALL_LIST = [
        0                    => 'Không xác định',
        self::CONNECTED_CALL => 'Đã kết nối',
        self::MISS_CALL      => 'Cuộc gọi nhỡ',
        self::BUSY_CALL      => 'Bận'
    ];

    const STOP_BY_CUSTOMER = 1;
    const STOP_BY_STAFF    = 2;

    const CALL_OUT = 1;
    const CALL_IN  = 2;

    const CALL_TYPE_LIST = [
        0              => 'Không xác định',
        self::CALL_OUT => 'Gọi ra',
        self::CALL_IN  => 'Gọi vào'
    ];

    public function user()
    {
        return $this->hasOne('Nh\User', 'id', 'user_id');
    }

    public function customer()
    {
        return $this->hasOne('Nh\Repositories\Customers\Customer', 'phone', 'to');
    }

}
