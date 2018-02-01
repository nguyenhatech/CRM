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
    public $fillable = ['client_id', 'code', 'type', 'amount', 'amount_max', 'quantity', 'quantity_per_user', 'date_start', 'date_end', 'status', 'image', 'title', 'content', 'description'];

    /**
     * Full path of images.
     */
    public $imgPath = 'storage/images/promotions';

    /**
     * Physical path of upload folder.
     */
    public $uploadPath = 'app/public/images/promotions';

    /**
     * Image width.
     */
    public $imgWidth = 500;

    /**
     * Image height.
     */
    public $imgHeight = 500;

    protected $dates = ['deleted_at'];

    const ENABLE = 1;
    const DISABLE = 0;

    const LIST_STATUS = [
        self::ENABLE => 'Đã kích hoạt',
        self::DISABLE => 'Chưa kích hoạt'
    ];

    const CASH = 0;
    const PERCENT = 1;
    const LIST_TYPE_PROMOTIONS = [
        self::CASH    => 'đ',
        self::PERCENT => '%'
    ];

    public function getImage()
    {
        return $this->image == '' ? '' : get_asset($this->imgPath . '/' . $this->image);
    }

    public function getStatusText()
    {
        return array_key_exists($this->status, self::LIST_STATUS) ? self::LIST_STATUS[$this->status] : 'Không xác định';
    }

    public function getTypeDiscountsText()
    {
        $type = (int) $this->type;
        return array_key_exists($type, self::LIST_TYPE_PROMOTIONS) ? self::LIST_TYPE_PROMOTIONS[$type] : 'Không xác định';
    }

    public static function getListTypePromotions()
    {
        return self::LIST_TYPE_PROMOTIONS;
    }

    public function checkExpired()
    {
        return Carbon::now() > $this->date_end ? 'Hết hạn' : 'Chưa hết hạn';
    }

    public function getQuantity()
    {
        return ! $this->quatity ? 'Không giới hạn' : $this->quatity;
    }
}
