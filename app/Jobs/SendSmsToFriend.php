<?php

namespace Nh\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nh\Repositories\Helpers\SpeedSMSAPI;
use Nh\Models\InviteFriend;

class SendSmsToFriend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inviteFriend = null;

     /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(InviteFriend $inviteFriend)
    {
        $this->inviteFriend = $inviteFriend;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sms = new SpeedSMSAPI();
        $phone_friend = $this->inviteFriend->phone_friend;
        $phone_owner = $this->inviteFriend->phone_owner;
        $content = '[Havaz] Ban da duoc thue bao '.$phone_owner.' moi cai dat ung dung dat ve, thue xe tren dien thoai. Tai ngay tai https://havaz.vn/app . Havaz - 1 ung dung cho moi neo duong, chi tiet lien he 1900 6763.';

        if ($phone_friend){
            $result = $sms->sendSMS((array) $phone_friend, $content, SpeedSMSAPI::SMS_TYPE_CSKH, "", 1);
            if ($result) {
                \Log::info('Tin nhan gioi thieu ban be: '. $phone_friend);
            } else {
                \Log::debug('Tin nhan gioi thieu ban be: '. $phone_friend);
            }
        }

    }
}
