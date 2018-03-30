<?php

namespace Nh\Repositories\LineCalls;

use Nh\Repositories\Entity;

class LineCall extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'vendor', 'line', 'user_id', 'phone_account', 'phone_account', 'password', 'profile_id'];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    public function user()
    {
        return $this->hasOne('Nh\User', 'id', 'user_id');
    }
}
