<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Leads\Lead;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class LeadTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'comments'
    ];

     public function transform(Lead $lead = null)
    {
        if (is_null($lead)) {
            return [];
        }

        $data = [
            'id'            => $lead->uuid,
            'name'          => $lead->name ? $lead->name : $lead->phone,
            'email'         => $lead->email,
            'phone'         => $lead->phone,
            'dob'           => $lead->dob,
            'gender'        => $lead->gender,
            'gender_text'   => Lead::GENDER_LIST[$lead->gender],
            'address'       => $lead->address,
            'city_id'       => $lead->city_id,
            'ip'            => $lead->ip,
            'facebook'      => $lead->facebook,
            'quality'       => $lead->quality,
            'source'        => $lead->source,
            'source_text'   => Lead::SOURCE_LIST[$lead->source],
            'utm_source'    => $lead->utm_source,
            'status'        => $lead->status,
            'status_text'   => Lead::STATUS_LIST[$lead->status],
            'created_at'    => $lead->created_at ? $lead->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'    => $lead->updated_at ? $lead->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeComments(Lead $lead = null)
    {
        if (is_null($lead)) {
            return $this->null();
        }

        return $this->collection($lead->comments, new CommentTransformer());
    }
}
