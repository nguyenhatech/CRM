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
        $this->customer = $customer;
        $this->promotion = $promotion;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        \Log::debug('Mail: ', [$this->promotion]);
        if ($this->promotion) {
            if ($this->customer->email) {
                $this->sendingEmail($this->customer, $this->promotion);
            }
            if ($this->customer->phone) {
                $this->sendingSMS($this->customer, $this->promotion);
            }
        }
    }

    private function sendingEmail($customer, $promotion)
    {
        try {
            $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
            $html = str_replace('***name***', $customer->name, $promotion->content);
            $mailer->revicer($customer->email)->subject('Havaz gửi tặng mã khuyến mại dành cho khách hàng đăng ký mới')->content($html)->sent();
            \Log::debug('Mail: ', [$response]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    private function sendingSMS($customer, $promotion)
    {
        try {
            $sms = new SpeedSMSAPI();
            $phone = [$customer->phone];
            $response = $sms->sendSMS($phone, $promotion->sms_template, SpeedSMSAPI::SMS_TYPE_CSKH, '', 1);
            \Log::debug('SMS: ', [$response]);
        } catch (\Exception $e) {
            return $e;
        }
    }
}
