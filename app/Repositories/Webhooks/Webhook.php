<?php

namespace Nh\Repositories\Webhooks;

use Nh\Repositories\Entity;

class Webhook extends Entity
{

    /**
     * Webhook constants
     */
    const WH_ALL         = 0;
    const WH_TRANSACTION = 1;
    const WH_REWARD_POINT = 2;
    const WH_CUSTOMER = 3;

    /**
     * [$event_list description]
     * @var [type]
     */
    public static $event_list = [
        self::WH_ALL          => 'Tất cả',
        self::WH_TRANSACTION  => 'Thông tin giao dịch',
        self::WH_REWARD_POINT => 'Điểm thưởng',
        self::WH_CUSTOMER     => 'Khách hàng',
    ];

    public static function getEvents() {
        return self::$event_list;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id', 'event_id', 'event', 'endpoint'];

    /**
     * Relationship with client which as a source client
     * @return Eloquent/Relations/Relation
     */
    public function client()
    {
        return $this->belongsTo('Nh\User', 'client_id')->withTrashed();
    }
}
