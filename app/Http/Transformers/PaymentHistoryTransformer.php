<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\PaymentHistories\PaymentHistory;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class PaymentHistoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'code'
    ];

     public function transform(PaymentHistory $paymentHistory = null)
    {
        if (is_null($paymentHistory)) {
            return [];
        }

        $data = [
            'id'           => $paymentHistory->uuid,
            'client_id'    => $paymentHistory->client_id,
            'customer_id'  => $paymentHistory->customer_id,
            'uuid'         => $paymentHistory->uuid,
            'booking_id'   => $paymentHistory->booking_id,
            'description'  => $paymentHistory->description,
            'total_amount' => $paymentHistory->total_amount,
            'total_point'  => $paymentHistory->total_point,
            'payment_at'   => $paymentHistory->payment_at ? $paymentHistory->payment_at->format('d-m-Y H:i:s') : null,
            'status'       => $paymentHistory->status,
            'status_txt'   => $paymentHistory->getStatusText(),
            'type'         => $paymentHistory->type,
            'type_txt'     => $paymentHistory->getTypeText(),
            'created_at'   => $paymentHistory->created_at ? $paymentHistory->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'   => $paymentHistory->updated_at ? $paymentHistory->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeCode(PaymentHistory $paymentHistory = null)
    {
        if (is_null($paymentHistory)) {
            return $this->null();
        }

        return $this->collection($paymentHistory->payment_history_codes, new PaymentHistoryCodeTransformer());
    }
}
