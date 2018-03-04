<?php

namespace Nh;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes, CanResetPasswordTrait;
    use EntrustUserTrait {
        EntrustUserTrait::restore insteadof SoftDeletes;
    }

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'name', 'email', 'phone', 'password', 'status', 'avatar', 'client_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

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

    const ENABLE = 1;
    const DISABLE = 0;

    const LIST_STATUS = [
        self::ENABLE => 'Đã kích hoạt',
        self::DISABLE => 'Chưa kích hoạt'
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
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isActive () {
        return $this->status == self::ENABLE;
    }

    public function getStatusText () {
        return self::LIST_STATUS[$this->status];
    }

    public function isSuperAdmin () {
        return $this->hasRole(['superadmin']);
    }

    public function isAdmin () {
        return $this->hasRole(['system.admin']) || $this->hasRole(['superadmin']);
    }

    public function getAvatar()
    {
        return $this->avatar == '' ? asset('avatar_default.png') : get_asset($this->imgPath . '/' . $this->avatar);
    }

    public function customers() {
        return $this->belongsToMany('Nh\Repositories\Customers\Customer', 'client_customers', 'client_id', 'customer_id');
    }
}
