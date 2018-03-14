<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Campaigns\Campaign;

class SendEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 5;

    protected $campaign;
    protected $customers;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign, $customers)
    {
        $this->campaign = $campaign;
        $this->customers = $customers;
    }

    /**
     * Gửi mail cho danh sách khách hàng theo campaign.
     * Sau khi gửi sẽ lấy campaignId của mail server lưu vào email_id trên campaign hệ thống
     *
     * @return void
     */
    public function handle()
    {
        $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
        $response;
        foreach ($this->customers as $key => $customer) {
            if ($customer->email) {
                $html = str_replace('***name***', $customer->name, $this->campaign->template);
                $response = $mailer->revicer($customer->email)->subject($this->campaign->name)->content($html)->campaign($this->campaign->name)->sendAsCampaign();
            }
        }
        if ($response->success()) {
            $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
            $campaign = \App::make('Nh\Repositories\Campaigns\CampaignRepository');
            $data = ['email_id' => $response->getData()['Sent'][0]['MessageID']];
            $campaign->update($this->campaign->id, $data);
        }
    }
}
