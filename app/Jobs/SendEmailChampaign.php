<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Campaigns\Campaign;
use Nh\Repositories\Customers\Customer;
use Nh\Repositories\EmailTemplates\EmailTemplate;

class SendEmailChampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    protected $campaign;
    protected $template;
    protected $customer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign, EmailTemplate $template, Customer $customer)
    {
        $this->campaign = $campaign;
        $this->template = $template;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->customer->email) {
            $html = str_replace('***name***', $this->customer->name, $this->template->template);
            $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
            $mailer->revicer($this->customer->email)->subject($this->campaign->name)->content($html)->sent();
        }
    }
}
