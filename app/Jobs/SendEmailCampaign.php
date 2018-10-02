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

    public $tries = 3;

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
        \Log::info('Bắt đầu chạy job gửi email');
        \Log::info($this->campaign->runtime);
        if ($this->campaign->runtime) {
            \Log::info('Gửi tự động');
            $sentEmails = $this->campaign->sent_emails->where('runtime', $this->campaign->runtime);
            if ($sentEmails->all() && $sentEmails->first()->runtime == $this->campaign->runtime) {
                \Log::info('Bắt đầu gửi');
                $this->sending($this->customers);
                // $response = null;
                // foreach ($this->customers as $key => $customer) {
                //     if ($customer->email) {
                //         $html = str_replace('***name***', $customer->name, $this->campaign->template);
                //         $response = $mailer->revicer($customer->email)->subject($this->campaign->name)->content($html)->campaign($this->campaign->name . '_' . $this->campaign->uuid)->sendAsCampaign();
                //     }
                // }
                // if (!is_null($response) && $response->success()) {
                //     $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
                //     $this->campaign->email_id = $response->getData()['Sent'][0]['MessageID'];
                //     $this->campaign->save();
                // }
            }
        } else {
            \Log::info('Gửi bằng tay');
            $this->sending($this->customers);
            // $response = null;
            // foreach ($this->customers as $key => $customer) {
            //     if ($customer->email) {
            //         $html = str_replace('***name***', $customer->name, $this->campaign->template);
            //         $response = $mailer->revicer($customer->email)->subject($this->campaign->name)->content($html)->campaign($this->campaign->name . '_' . $this->campaign->uuid)->sendAsCampaign();
            //     }
            // }
            // \Log::info($response);
            // if (!is_null($response) && $response->success()) {
            //     $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
            //     $this->campaign->email_id = $response->getData()['Sent'][0]['MessageID'];
            //     $this->campaign->save();
            // }
        }
    }

    private function sending($customer) {
        $mailer     = new \Nh\Repositories\Helpers\MailJetHelper();
        $response;
        foreach ($this->customers as $key => $customer) {
            if ($customer->email) {
                $html = str_replace('***name***', $customer->name, $this->campaign->template);
                $response = $mailer->revicer($customer->email)->subject($this->campaign->name)->content($html)->campaign($this->campaign->name . '_' . $this->campaign->uuid)->sendAsCampaign();
                // dd($response->success());
            }
        }
        if (!is_null($response) && $response->success()) {
            $messageInfo  = $mailer->getMessageInfo($response->getData()['Sent'][0]['MessageID']);
            $this->campaign->email_id = $response->getData()['Sent'][0]['MessageID'];
            $this->campaign->save();
        }
    }
}