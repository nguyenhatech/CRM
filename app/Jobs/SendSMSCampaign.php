<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Campaigns\Campaign;
use Nh\Repositories\Helpers\SpeedSMSAPI;

class SendSMSCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 5;

    public $campaign;
    public $customers;
    public $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign, $customers, $content)
    {
        $this->campaign = $campaign;
        $this->customers = $customers;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $phones = [];
        foreach ($this->customers as $key => $customer) {
            if ($customer->phone) {
                array_push($phones, $customer->phone);
            }
        }
        $sms = new SpeedSMSAPI();
        $result = $sms->sendSMS($phones, $this->content, SpeedSMSAPI::SMS_TYPE_CSKH, "", 1);
    }
}
