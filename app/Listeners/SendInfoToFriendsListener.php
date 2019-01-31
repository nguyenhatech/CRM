<?php

namespace Nh\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nh\Events\EventSendInfoToFriends;

class SendInfoToFriendsListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(EventSendInfoToFriends $event)
    {
        $inviteFriend = $event->inviteFriend;

        // Gui tin nhan cho friends
        $jobSms = new \Nh\Jobs\SendSmsToFriend($inviteFriend);
        dispatch($jobSms)->onQueue(env('APP_NAME'));
    }
}
