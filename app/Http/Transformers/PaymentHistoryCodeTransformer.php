<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\PaymentHistoryCodes\PaymentHistoryCode;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class PaymentHistoryCodeTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
    ];

     public function transform(PaymentHistoryCode $paymentHistoryCode = null)
    {
        if (is_null($paymentHistoryCode)) {
            return [];
        }

        $data = [
            'id'           => $paymentHistoryCode->id,
            'code'         => $paymentHistoryCode->promotion_code,
            'type'         => $paymentHistoryCode->type_check
        ];

        return $data;
    }
}
