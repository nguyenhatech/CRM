<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\PhoneCallHistories\PhoneCallHistory;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class PhoneCallHistoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'user',
        'customer'
    ];

    public function transform(PhoneCallHistory $call = null)
    {
        if (is_null($call)) {
            return [];
        }

        $data = [
            'id'             => $call->uuid,
            'user_id'        => $call->user_id,
            'transaction_id' => $call->transaction_id,
            'from'           => $call->from,
            'to'             => $call->to,
            'hotline'        => $call->hotline,
            'type'           => $call->type,
            'call_type'      => $call->call_type,
            'status'         => $call->status,
            'type_text'      => $call->status == 1 ? 'Mobile' : 'Device',
            'call_type_text' => PhoneCallHistory::CALL_TYPE_LIST[$call->call_type],
            'status_text'    => PhoneCallHistory::STATUS_CALL_LIST[$call->status],
            'stop_by'        => $call->name,
            'start_time'     => $call->start_time ? date('Y-m-d H:i:s', intval($call->start_time)/1000) : null,
            'end_time'       => $call->end_time ? date('Y-m-d H:i:s', intval($call->end_time)/1000) : null,
            'duration'       => $this->durationCaculator($call->start_time, $call->end_time),
            'created_at'     => $call->created_at ? $call->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'     => $call->updated_at ? $call->created_at->format('Y-m-d H:i:s') : null
        ];

        return $data;
    }

    public function durationCaculator($start_time, $end_time)
    {
        if ($start_time && $end_time) {
            return intval($call->end_time)/1000 - intval($call->start_time)/1000;
        }
        return 'Không xác định';
    }

    public function includeUser(PhoneCallHistory $call = null)
    {
        if (is_null($call)) {
            return $this->null();
        }

        return $this->item($call->user, new UserTransformer());
    }

    public function includeCustomer(PhoneCallHistory $call = null)
    {
        if (is_null($call)) {
            return $this->null();
        }

        return $this->item($call->customer, new CustomerTransformer());
    }
}
