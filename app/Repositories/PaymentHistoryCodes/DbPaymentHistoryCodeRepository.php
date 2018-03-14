<?php

namespace Nh\Repositories\PaymentHistoryCodes;
use Nh\Repositories\BaseRepository;

class DbPaymentHistoryCodeRepository extends BaseRepository implements PaymentHistoryCodeRepository
{
    public function __construct(PaymentHistoryCode $paymentHistoryCode)
    {
        $this->model = $paymentHistoryCode;
    }

}
