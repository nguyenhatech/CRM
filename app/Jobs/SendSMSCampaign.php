<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Campaigns\Campaign;
use Nh\Repositories\Campaigns\CampaignRepository;
use Nh\Repositories\Helpers\SpeedSMSAPI;

class SendSMSCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;

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
        $result = $sms->sendSMS(['01682601994'], $this->content, SpeedSMSAPI::SMS_TYPE_CSKH, "", 1);
        // Cập nhật SMS ID vào campaign
        if ($result['status'] == 'success') {
            $campaign = \App::make('Nh\Repositories\Campaigns\CampaignRepository');
            $data = ['sms_id' => $result['data']['tranId']];
            $campaign->update($this->campaign->id, $data);
        }
    }
}
