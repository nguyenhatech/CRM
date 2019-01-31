<?php

namespace Nh\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Nh\Models\InviteFriend;

class EventSendInfoToFriends
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inviteFriend;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(InviteFriend $inviteFriend)
    {
        $this->inviteFriend = $inviteFriend;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // return new PrivateChannel('channel-name');
    }
}
