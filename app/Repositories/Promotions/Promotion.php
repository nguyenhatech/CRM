<?php

namespace Nh\Repositories\Promotions;

use Nh\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Promotion extends Entity
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['client_id', 'code', 'type', 'amount', 'amount_max', 'quantity', 'quantity_per_user', 'date_start', 'date_end', 'status'];

    protected $dates = ['deleted_at'];

    const ENABLE = 1;
    const DISABLE = 0;

    const LIST_STATUS = [
        self::ENABLE => 'Đã kích hoạt',
        self::DISABLE => 'Chưa kích hoạt'
    ];

    const CASH = 0;
    const PERCENT = 1;
    const LIST_TYPE_DISCOUNTS = [
        self::CASH    => 'Giảm theo tiền mặt',
        self::PERCENT => 'Giảm theo phần trăm'
    ];

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getTypeDiscountsText()
    {
        $type = (int) $this->type;
        return array_key_exists($type, self::LIST_TYPE_DISCOUNTS) ? self::LIST_TYPE_DISCOUNTS[$type] : 'Không xác định';
    }

    public function checkExpired()
    {
        return Carbon::now() > $this->date_end ? 'Hết hạn' : 'Chưa hết hạn';
    }
}
