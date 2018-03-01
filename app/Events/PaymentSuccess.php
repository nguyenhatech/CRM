<?php

namespace Nh\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nh\Repositories\PaymentHistories\PaymentHistory;

class PaymentSuccess
{
    use SerializesModels;
    public $paymentHistory;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PaymentHistory $paymentHistory)
    {
        $this->paymentHistory = $paymentHistory;
    }
}
