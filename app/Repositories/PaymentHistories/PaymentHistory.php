<?php

namespace Nh\Repositories\PaymentHistories;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentHistory extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id', 'customer_id', 'uuid', 'description', 'total_amount', 'promotion_code', 'total_point', 'payment_at', 'status', 'type', 'booking_id'];

    protected $dates = ['payment_at'];

    const PAY_PENDDING = 0; // Chờ giao dịch
    const PAY_SUCCESS = 1; // Thành công
    const PAY_CANCEL = 2; // Hủy

    const LIST_STATUS = [
        self::PAY_PENDDING => 'Chờ giao dịch',
        self::PAY_SUCCESS  => 'Thành công',
        self::PAY_CANCEL   => 'Hủy'
    ];

    const TYPE_DIRECT = 0; // giao dịch trực tiếp
    const TYPE_CONFIRM = 1; // giao dịch chờ xác nhận


    const LIST_TYPE = [
        self::TYPE_DIRECT => 'Giao dịch đã được thanh toán',
        self::TYPE_CONFIRM => 'Giao dịch chờ xác nhận'
    ];

    public static function list_status() {
        return [
            PAY_PENDDING => 'Chờ giao dịch',
            PAY_SUCCESS => 'Giao dịch thành công',
            PAY_CANCEL => 'Giao dịch bị hủy',
        ];
    }

    protected static function boot()
    {
        static::created(function ($model) {
            $model->uuid = \Hashids::encode($model->id);
            if (getCurrentUser()) {
                $model->client_id = getCurrentUser()->id;
            }
            if ($model->type == self::TYPE_DIRECT) {
                $model->status = self::PAY_SUCCESS;
                $model->payment_at = \Carbon\Carbon::now();
            }
            if ($model->total_amount < 0) {
                $setting = \Nh\Repositories\Settings\Setting::find(1);
                $model->total_point = floor(abs($model->total_amount) / $setting->amount_per_score);
            }
            $model->save();
        });

        parent::boot();
    }

    public function statusText() {
        return self::list_status()[$this->status];
    }

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getTypeText()
    {
        return array_key_exists($this->type, self::LIST_TYPE) ? self::LIST_TYPE[$this->type] : 'Không xác định';
    }

    public function customer () {
        return $this->belongsTo('Nh\Repositories\Customers\Customer');
    }

    public function client () {
        return $this->belongsTo('Nh\User', 'client_id');
    }

    public function payment_history_codes()
    {
        return $this->hasMany('Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode', 'payment_history_id', 'id');
    }
}
