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
    public $fillable = ['uuid', 'name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'source', 'level', 'avatar', 'last_payment', 'identification_number', 'city_id'];

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

    const JOBS = [
        1 => 'Học sinh/sinh viên',
        2 => 'Hành chính',
        3 => 'Công nghệ thông tin',
        4 => 'Kinh doanh/ Marketing /Dịch vụ khách hàng',
        5 => 'Kế toán/ Tài chính',
        6 => 'Sản xuất công nghiệp/nông nghiệp',
        7 => 'Cơ khí/ Điện tử/ Vận tải',
        8 => 'Ngành nghề khác'
    ];

    const SOURCE = [
        0 => 'CRM',
        1 => 'WEB',
        2 => 'ERP',
        3 => 'APP USER',
        4 => 'APP DRIVE'
    ];

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            $model->save();
        });

        // Tạm bỏ đi vì HSHV không cần
        // static::addGlobalScope('customers', function (Builder $builder) {
        //     if (getCurrentUser() && !getCurrentUser()->isAdmin()) {
        //         $builder->whereHas('client', function ($builder) {
        //             $builder->where('id', getCurrentUser()->id);
        //         });
        //     }
        // });

        parent::boot();
    }

    public function getAvatar()
    {
        return $this->avatar == '' ? get_asset('avatar_default.png') : get_asset($this->imgPath . '/' . $this->avatar);
    }

    public function getTotalAmount() {
        return $this->payments()
                ->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_FINISH)
                ->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_amount');
    }

    public function getTotalPoint() {
        return $this->payments()
                ->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_FINISH)
                ->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_SUCCESS)->sum('total_point');
    }

    public function getTotalTrips() {
        $result = $this->payments()->where('client_id', 0)->whereNull('booking_id')->whereDate('payment_at', '2018-04-25')->count();
        if($result == 1) {
            return 2;
        }
        return $this->payments()->where('status', \Nh\Repositories\PaymentHistories\PaymentHistory::PAY_FINISH)->sum('total_point');
    }

    public function levelText() {
        if (array_key_exists($this->level, list_level())) {
            return list_level()[$this->level];
        }
        return null;
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

    public function getSource()
    {
        if (array_key_exists($this->source, self::SOURCE)) {
            return self::SOURCE[$this->source];
        }
        return null;
    }

    public function tags()
    {
        return $this->belongsToMany('Nh\Repositories\Tags\Tag', 'customer_tag');
    }
}
