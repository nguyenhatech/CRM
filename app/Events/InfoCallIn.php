<?php

namespace Nh\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Nh\Repositories\PhoneCallHistories\PhoneCallHistory;

class InfoCallIn implements ShouldQueue
{
    use SerializesModels;
    public $call;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PhoneCallHistory $call)
    {
        $this->call = $call;
    }
}
