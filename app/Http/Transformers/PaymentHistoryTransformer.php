<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\PaymentHistories\PaymentHistory;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class PaymentHistoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        // 'roles'
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
            'description'  => $paymentHistory->description,
            'total_amount' => $paymentHistory->total_amount,
            'total_point'  => $paymentHistory->total_point,
            'payment_at'   => $paymentHistory->payment_at,
            'status'       => $paymentHistory->status,
            'type'         => $paymentHistory->type,
            'created_at'   => $paymentHistory->created_at ? $paymentHistory->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'   => $paymentHistory->updated_at ? $paymentHistory->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    // public function includeRoles(PaymentHistory $paymentHistory = null)
    // {
    //     if (is_null($paymentHistory)) {
    //         return $this->null();
    //     }

    //     return $this->collection($paymentHistory->roles, new RoleTransformer());
    // }
}
