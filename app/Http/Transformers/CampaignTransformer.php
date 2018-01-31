<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Campaigns\Campaign;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

     public function transform(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return [];
        }

        $data = [
            'id'          => $campaign->id,
            'client_id'   => $campaign->client_id,
            'template_id' => $campaign->template_id,
            'cgroup_id'   => $campaign->cgroup_id,
            'uuid'        => $campaign->uuid,
            'name'        => $campaign->name,
            'description' => $campaign->description,
            'start_date'  => $campaign->start_date ? $campaign->start_date->format('d-m-Y H:i:s') : null,
            'end_date'    => $campaign->end_date ? $campaign->end_date->format('d-m-Y H:i:s') : null,
            'status'      => $campaign->status,
            'status_txt'  => $campaign->getStatusText(),
            'created_at'  => $campaign->created_at ? $campaign->created_at->format('d-m-Y H:i:s') : null,
        ];

        return $data;
    }
}
