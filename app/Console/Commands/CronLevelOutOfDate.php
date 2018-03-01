<?php

namespace Nh\Console\Commands;

use Illuminate\Console\Command;
use Nh\Repositories\PaymentHistories\PaymentHistory;
use Nh\Repositories\Customers\Customer;

class CronLevelOutOfDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tụt hạng khách hàng sau 3 tháng không phát sinh giao dịch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, PaymentHistory $paymentHistory)
    {
        parent::__construct();
        $this->customer = $customer;
        $this->paymentHistory = $paymentHistory;
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $customers = $this->customer->whereRaw('DATEDIFF(NOW(), last_payment) >= 92g')->get();
        foreach ($customers as $customer) {
            $paymentHistory = $this->paymentHistory->create([
                'customer_id'  => $customer->id,
                'description'  => 'Tụt hạng sau 3 tháng không có giao dịch',
                'total_amount' => 0,
                'total_point'  => -($customer->getTotalPoint() - (list_level_point()[$customer->level] - 1000)),
                'payment_at'   => \Carbon\Carbon::now(),
                'status'       => PaymentHistory::PAY_SUCCESS,
                'type'         => PaymentHistory::TYPE_DIRECT
            ]);

            event(new \Nh\Events\PaymentSuccess($paymentHistory));
            event(new \Nh\Events\UpdateLevelCustomer($paymentHistory->customer));
            event(new \Nh\Events\DownLevelCustomer($paymentHistory->customer));
        }
    }
}
