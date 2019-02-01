<?php

namespace Nh\Events\V2;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CustomerReviewAppEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $phone = '';
    public $uuid = '';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phone, $uuid)
    {
        $this->phone = $phone;
        $this->uuid = $uuid;
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
