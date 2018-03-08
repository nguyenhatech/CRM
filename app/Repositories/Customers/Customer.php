<?php

namespace Nh\Repositories\Customers;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Entity
{
    use SoftDeletes;
    protected $dates = ['deleted_at', 'dob', 'last_payment'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['uuid', 'name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'source', 'level', 'avatar', 'last_payment', 'identification_number'];

    /**
     * Full path of images.
     */
    public $imgPath = 'storage/images/avatars';

    /**
     * Physical path of upload folder.
     */
    public $uploadPath = 'app/public/images/avatars';

    /**
     * Image width.
     */
    public $imgWidth = 200;

    /**
     * Image height.
     */
    public $imgHeight = 200;

    const JOBS = ['Khách du lịch', 'Nhân viên văn phòng', 'Sinh viên', 'Buôn bán', 'Lao động tự do'];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        static::addGlobalScope('customers', function (Builder $builder) {
            if (getCurrentUser() && !getCurrentUser()->isAdmin()) {
                $builder->whereHas('client', function ($builder) {
                    $builder->where('id', getCurrentUser()->id);
                });
            }
        });

        parent::boot();
    }

    public function getAvatar()
    {
        return $this->avatar == '' ? asset('avatar_default.png') : get_asset($this->imgPath . '/' . $this->avatar);
    }

    public function getTotalAmount() {
        return $this->payments()->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_amount');
    }

    public function getTotalPoint() {
        return $this->payments()->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_point');
    }

    public function levelText() {
        return list_level()[$this->level];
    }

    public static function getListLevel()
    {
        return list_level();
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

    public function groups()
    {
        return $this->belongsToMany('Nh\Repositories\Cgroups\Cgroup', 'customer_cgroups', 'customer_id', 'cgroup_id');
    }
}
