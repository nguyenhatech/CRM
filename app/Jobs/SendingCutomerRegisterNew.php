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
            $this->sendingEmail($this->customer, $promotion);
        }
        if ($this->customer->phone) {
            $this->sendingSMS($this->customer, $promotion);
        }
    }

    private function sendingEmail($customer, $promotion)
    {
        try {
            $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
            $mailer->revicer($customer->email)->subject('Đăng ký tài khoản thành công')->content($promotion->content)->campaign('send_email_register_account_new_'.time())->sendAsCampaign();
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