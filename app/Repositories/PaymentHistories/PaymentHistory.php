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
    public $fillable = ['client_id', 'customer_id', 'uuid', 'description', 'total_amount', 'total_point', 'payment_at', 'status', 'type'];

    const PAY_PENDDING = 0; // Chờ giao dịch
    const PAY_SUCCESS = 1; // Thành công
    const PAY_CANCEL = 2; // Hủy

    const TYPE_DIRECT = 0; // giao dịch trực tiếp
    const TYPE_CONFIRM = 1; // giao dịch chờ xác nhận

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
            $model->client_id = getCurrentUser()->id;
            if ($model->type == self::TYPE_DIRECT) {
                $model->status = self::PAY_SUCCESS;
            }
            if ($model->total_amount < 0) {
                $model->total_point = floor(-$model->total_amount / 1000);
            }
            $model->save();
        });

        static::updating(function ($model) {
            $old = $model->getDirty();
            if ($old['status'] != $model->status && $model->status == self::PAY_SUCCESS) {
                $model->payment_at = \Carbon\Carbon::now()->format('Y-m-d');
                // $model->save();
            }
        });

        parent::boot();
    }

    public function statusText() {
        return self::list_status()[$this->status];
    }

    public function customer () {
        return $this->belongsTo('Nh\Repositories\Customers\Customer');
    }

    public function client () {
        return $this->belongsTo('Nh\User', 'client_id');
    }
}
