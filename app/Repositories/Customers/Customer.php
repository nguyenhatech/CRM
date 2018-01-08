<?php

namespace Nh\Repositories\Customers;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Entity
{
    use SoftDeletes;
    protected $dates = ['deleted_at', 'dob'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'source', 'level', 'avatar'];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        parent::boot();
    }

    public function getTotalAmount() {
        return $this->payments()->where('status', \Nh\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_amount');
    }

    public function getTotalPoint() {
        return $this->payments()->where('status', \Nh\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_point');
    }

    public function levelText() {
        return list_level()[$this->level];
    }

    public function sexText() {
        return list_sex()[$this->sex];
    }

    public function payments() {
        return $this->hasMany('Nh\Repositories\PaymentHistories\PaymentHistory');
    }

    public function client() {
        return $this->belongsToMany('Nh\User', 'client_customers', 'customer_id', 'client_id');
    }
}