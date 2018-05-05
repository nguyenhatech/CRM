<?php

namespace Nh\Console\Commands;

use Illuminate\Console\Command;
use Nh\Repositories\Campaigns\Campaign;
use Nh\Jobs\SendEmailCampaign;
use Illuminate\Support\Carbon;

class CronSendEmailCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:campaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chạy lệnh tự động gửi email của các chiến dịch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $campaigns = $this->campaign->whereDate('runtime', date('Y-m-d'))->get();
        foreach ($campaigns as $key => $campaign) {
            $sentEmail = $campaign->sent_emails->where('runtime', $campaign->runtime);
            if (!$sentEmail->all()) {
                \Log::info('Cron send email campaign');
                $time = Carbon::parse($campaign->runtime);
                $time = $time->timestamp - time();
                if ($time < 1) {
                    $time = 1;
                }

                if ($campaign->target_type == Campaign::GROUP_TARGET || $campaign->target_type == Campaign::FILTER_TARGET) {
                    $customers = $campaign->cgroup->customers;
                } else {
                    $customers = $campaign->customers;
                }

                $customerChunks = $customers->chunk(1000);
                foreach ($customerChunks as $chunk) {
                    $job = new SendEmailCampaign($campaign, $chunk);
                    dispatch($job)->delay(now()->addSeconds($time))->onQueue(env('APP_NAME'));
                }
            }
        }
    }
}
