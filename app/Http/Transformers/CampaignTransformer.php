<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Campaigns\Campaign;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'client',
        'cgroup'
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
            'start_date'  => $campaign->start_date ? $campaign->start_date->format('d-m-Y') : null,
            'end_date'    => $campaign->end_date ? $campaign->end_date->format('d-m-Y') : null,
            'status'      => $campaign->status,
            'status_txt'  => $campaign->getStatusText(),
            'created_at'  => $campaign->created_at ? $campaign->created_at->format('d-m-Y H:i:s') : null,
        ];

        return $data;
    }

    public function includeClient(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return $this->null();
        }

        return $this->item($campaign->client, new UserTransformer());
    }

    public function includeCgroup(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return $this->null();
        }

        return $this->item($campaign->cgroup, new CgroupTransformer());
    }
}
