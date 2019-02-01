<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Helpers\SpeedSMSAPI;
use Nh\Models\InviteFriend;
use Carbon\Carbon;

class SendSmsToPresenter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inviteFriend = null;
    public $code = null;
    public $dayApplied = null;
    public $moneyApplied = null;
     /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(InviteFriend $inviteFriend, $code, $dayApplied, $moneyApplied)
    {
        $this->inviteFriend = $inviteFriend;
        $this->code = $code;
        $this->dayApplied = $dayApplied;
        $this->moneyApplied = $moneyApplied;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sms = new SpeedSMSAPI();
        $phone_owner = $this->inviteFriend->phone_owner;
        $end = Carbon::today()->addDays($this->dayApplied)->format('d/m/Y');

        $content = 'Chuc mung ban gioi thieu thanh cong app HAVAZ. HAVAZ danh tang ban 1 ma  '.$this->code.' tri gia '.$this->moneyApplied.'d.Chi tiet lien he 19006763';
        //$content = 'Havaz tang ban ma khuyen mai '.$this->code.' giam gia '.$this->moneyApplied.' vnd sau khi moi thue bao '. $this->inviteFriend->phone_friend .' cai dat ung dung Havaz thanh cong, HSD '. $end .'. Havaz - 1 ung dung cho moi neo duong, chi tiet lien he 1900 6763.';

        if ($phone_owner){
            $result = $sms->sendSMS((array) $phone_owner, $content, SpeedSMSAPI::SMS_TYPE_CSKH, "", 1);
        }
    }
}
