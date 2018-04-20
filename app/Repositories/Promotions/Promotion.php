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
    public $fillable = ['client_id', 'code', 'type', 'amount', 'amount_segment', 'amount_max', 'quantity', 'quantity_per_user', 'date_start', 'date_end', 'status', 'image', 'title', 'content', 'description', 'target_type', 'cgroup_id'];

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

    const NORMAL_TICKET    = 1;
    const VIP_TICKET       = 2;
    const SUPER_VIP_TICKET = 3;
    const LIST_TARGET_TYPE = [
        0                   => 'Áp dụng cho tất cả các chuyến',
        self::NORMAL_TICKET => 'Thường',
        self::VIP_TICKET    => 'Vip',
        self::SUPER_VIP_TICKET     => 'Siêu vip'
    ];

    const LIST_TARGET_TYPE_V2 = [
        0                   => 'Áp dụng cho tất cả các hạng xe',
        self::NORMAL_TICKET => 'Áp dụng cho khách đi xe hạng Thường',
        self::VIP_TICKET    => 'Áp dụng cho khách đi xe hạng Vip',
        self::SUPER_VIP_TICKET     => 'Áp dụng cho khách đi xe hạng Siêu Vip'
    ];

    const ROUTE = 1; // Hình thức khách hàng đi theo tuyến
    const SEGMENT = 2; // Hình thức khách hàng đi theo chặng

    // Danh sách hình thức di chuyển của khách
    const LIST_FORM_MOVE = [
        self::ROUTE   => 'Giảm giá cho khách hàng đi cả tuyến',
        self::SEGMENT => 'Giảm giá cho khách hàng đi theo chặng'
    ];

    public function getImage()
    {
        if (strrpos($this->image, 'http://') === 0 || strrpos($this->image, 'https://') === 0) {
            return $this->image;
        }
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

    /**
     * Trả về hình thức di chuyển của khách
     * @return [type] [description]
     */
    public function getFormMovesText($typeMove)
    {
        $typeMove = (int) $typeMove;
        return array_key_exists($typeMove, self::LIST_FORM_MOVE) ? self::LIST_FORM_MOVE[$typeMove] : 'Không xác định';
    }

    /**
     * Trả về hạng xe khách đi
     * @return [type] [description]
     */
    public function getListTargetTypeText($typeTaget)
    {
        $typeTaget = (int) $typeTaget;
        return array_key_exists($typeTaget, self::LIST_TARGET_TYPE_V2) ? self::LIST_TARGET_TYPE_V2[$typeTaget] : 'Không xác định';
    }
}
