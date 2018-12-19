<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Helpers\SpeedSMSAPI;
use Nh\Repositories\Customers\Customer;

class SendingCutomerRegisterNew implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $promotion;
    protected $customer;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $customer, $promotion)
    {
        // dd($customer);
        $this->customer = $customer;
        $this->promotion = $promotion;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $promotion = $this->promotion->getPromotionByAccountNew();
        if ($this->customer->email) {
            \Log::info('đã gửi email');
            $this->sendingEmail($this->customer, $promotion);
        }
        if ($this->customer->phone) {
            \Log::info('đã gửi sms');
            $this->sendingSMS($this->customer, $promotion);
        }
    }

    private function sendingEmail($customer, $promotion)
    {
        try {
            $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
            $mailer->revicer($customer->email)->subject('Havaz gửi tặng mã khuyến mại dành cho khách hàng đăng ký mới')->content($promotion->content)->sent();
        } catch (\Exception $e) {
            return $e;
        }
    }

    private function sendingSMS($customer, $promotion)
    {
        try {
            $sms = new SpeedSMSAPI();
            $phone = [$customer->phone];
            $sms->sendSMS($phone, $promotion->sms_template, SpeedSMSAPI::SMS_TYPE_CSKH, '', 1);
        } catch (\Exception $e) {
            return $e;
        }
    }
}
