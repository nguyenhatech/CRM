<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Settings\Setting;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class SettingTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

     public function transform(Setting $setting = null)
    {
        if (is_null($setting)) {
            return [];
        }

        $data = [
            'id'                                => $setting->id,
            'special_day'                      => count(json_decode($setting->special_day)) ? json_decode($setting->special_day) : null,
            'disable_promotion_special_day'     => $setting->disable_promotion_special_day,
            'disable_promotion_special_day_txt' => $setting->getStatusTextPromotionSpecialDay(),
            'disable_sms_special_day'           => $setting->disable_sms_special_day,
            'levels'                            => [
                'normal'  => $setting->level_normal,
                'sliver'  => $setting->level_sliver,
                'gold'    => $setting->level_gold,
                'diamond' => $setting->level_diamond,
            ],
            'created_at'                        => $setting->created_at ? $setting->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }
}
