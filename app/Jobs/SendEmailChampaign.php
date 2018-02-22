<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Campaigns\Campaign;

class SendEmailChampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->customers as $key => $customer) {
            if ($customer->email) {
                $html = str_replace('***name***', $customer->name, $this->campaign->template);
                $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
                $mailer->revicer($customer->email)->subject($this->campaign->name)->content($html)->sent();
            }
        }
    }
}
