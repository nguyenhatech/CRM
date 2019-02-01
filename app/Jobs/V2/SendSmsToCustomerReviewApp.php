<?php

namespace Nh\Jobs\V2;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Nh\Repositories\Helpers\SpeedSMSAPI;
use Nh\Repositories\Customers\Customer;
use Carbon\Carbon;

class SendSmsToCustomerReviewApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customer = null;
    public $code = null;
    public $dayApplied = null;
    public $moneyApplied = null;
    public $uuid = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, $code, $dayApplied, $moneyApplied, $uuid)
    {
        $this->customer = $customer;
        $this->code = $code;
        $this->dayApplied = $dayApplied;
        $this->moneyApplied = $moneyApplied;
        $this->uuid = $uuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sms = new SpeedSMSAPI();
        $phone = $this->customer->phone;
        $end = Carbon::today()->addDays($this->dayApplied)->format('d/m/Y');

        $content = 'Havaz tang ban ma khuyen mai '.$this->code.' giam gia '.$this->moneyApplied.' sau khi danh gia chuyen di thanh cong (ma booking '.strtoupper($this->uuid).').  Havaz - 1 ung dung cho moi neo duong, chi tiet lien he 1900 6763.';

        if ($phone){
            $result = $sms->sendSMS((array) $phone, $content, SpeedSMSAPI::SMS_TYPE_CSKH, "", 1);
        }
    }
}
