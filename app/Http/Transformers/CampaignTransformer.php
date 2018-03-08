<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Campaigns\Campaign;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CampaignTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'client',
        'cgroup',
        'template',
        'customers'
    ];

    public function transform(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return [];
        }

        $data = [
            'id'            => $campaign->uuid,
            'client_id'     => $campaign->client_id,
            'template_id'   => $campaign->template_id,
            'template'      => $campaign->template,
            'sms_template'  => $campaign->sms_template,
            'cgroup_id'     => $campaign->cgroup_id,
            'target_type'   => $campaign->target_type,
            'customers'     => $campaign->customers,
            'uuid'          => $campaign->uuid,
            'name'          => $campaign->name,
            'description'   => $campaign->description,
            'start_date'    => $campaign->start_date ? $campaign->start_date->format('Y-m-d H:i:s') : null,
            'end_date'      => $campaign->end_date ? $campaign->end_date->format('Y-m-d H:i:s') : null,
            'runtime'       => $campaign->runtime ? $campaign->runtime : null,
            'status'        => $campaign->status,
            'status_txt'    => $campaign->getStatusText(),
            'period'        => $campaign->period,
            'period_text'   => $campaign->getPeriodText(),
            'created_at'    => $campaign->created_at ? $campaign->created_at->format('Y-m-d H:i:s') : null,
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

    public function includeTemplate(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return $this->null();
        }

        return $this->item($campaign->email_template, new EmailTemplateTransformer());
    }

    public function includeCustomers(Campaign $campaign = null)
    {
        if (is_null($campaign)) {
            return $this->null();
        }

        return $this->collection($campaign->customers, new CustomerTransformer());
    }
}
