<?php

namespace Nh\Repositories\Settings;

use Nh\Repositories\Entity;

class Setting extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'special_day',
        'disable_promotion_special_day',
        'disable_sms_special_day',
        'level_normal', 'level_sliver', 'level_gold', 'level_diamond'
    ];

    const ENABLE_PROMOTION_SPECIAL_DAY  = 0;
    const DISABLE_PROMOTION_SPECIAL_DAY = 1;

    const LIST_STATUS_PROMOTION_SPECIAL_DAY = [
        self::ENABLE_PROMOTION_SPECIAL_DAY => 'Cho khuyến mãi vào ngày đặc biệt',
        self::DISABLE_PROMOTION_SPECIAL_DAY => 'Không cho khuyến mãi vào ngày đặc biệt'
    ];


    public function getStatusTextPromotionSpecialDay()
    {
        return array_key_exists($this->disable_promotion_special_day, self::LIST_STATUS_PROMOTION_SPECIAL_DAY) ? self::LIST_STATUS_PROMOTION_SPECIAL_DAY[$this->disable_promotion_special_day] : 'Không xác định';
    }
}
