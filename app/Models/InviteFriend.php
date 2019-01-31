<?php

namespace Nh\Models;

use Illuminate\Database\Eloquent\Model;

class InviteFriend extends Model
{
    protected $fillable = ['phone_owner', 'phone_friend', 'is_customer'];

    protected static function boot()
    {
        static::created(function ($model) {
            event(new \Nh\Events\EventSendInfoToFriends($model));
        });

        parent::boot();
    }

    protected $casts =  [
        'is_customer' => 'boolean'
    ];
}
