<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Promotions\Promotion;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class PromotionTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'cgroup'
    ];

     public function transform(Promotion $promotion = null)
    {
        if (is_null($promotion)) {
            return [];
        }

        $data = [
            'id'                => $promotion->uuid,
            'client_id'         => $promotion->client_id,
            'code'              => strtoupper($promotion->code),
            'type'              => $promotion->type,
            'target_type'       => $promotion->target_type,
            'merchants'         => $promotion->merchants,
            'type_txt'          => $promotion->getTypeDiscountsText(),
            'cgroup_id'         => $promotion->cgroup ? $promotion->cgroup->uuid : '',
            'image'             => $promotion->image,
            'image_path'        => $promotion->getImage(),
            'title'             => $promotion->title,
            'description'       => $promotion->description,
            'content'           => $promotion->content ? $promotion->content : '',
            'amount'            => $promotion->amount,
            'amount_segment'    => $promotion->amount_segment,
            'amount_max'        => $promotion->amount_max,
            'quantity'          => $promotion->quantity,
            'quantity_per_user' => $promotion->quantity_per_user,
            'date_start'        => $promotion->date_start,
            'date_end'          => $promotion->date_end,
            'limit_time_type'   => $promotion->limit_time_type,
            'status'            => $promotion->status,
            'status_txt'        => $promotion->getStatusText(),
            'created_at'        => $promotion->created_at ? $promotion->created_at->format('Y-m-d H:i:s') : null,
        ];

        return $data;
    }

    public function includeCgroup(Promotion $promotion = null)
    {
        if (is_null($promotion)) {
            return $this->null();
        }

        return $this->item($promotion->cgroup, new CgroupTransformer());
    }
}
