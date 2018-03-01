<?php

namespace Nh\Repositories\Webhooks;

use Nh\Repositories\Entity;

class Webhook extends Entity
{

    /**
     * Webhook constants
     */
    const WH_ALL             = 0;

    /**
     * [$event_list description]
     * @var [type]
     */
    public static $event_list = [
        self::WH_ALL             => 'Tất cả'
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
