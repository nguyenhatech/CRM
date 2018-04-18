<?php

namespace Nh\Listeners;

use Nh\Events\InfoCallIn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Nh\Repositories\Leads\Lead;

class CreateNewLead implements ShouldQueue
{
    use InteractsWithQueue;
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
     * Nếu số điện thoại chưa phải là khách hàng thì tạo lead mới.
     *
     * @param  InfoCallIn  $event
     * @return void
     */
    public function handle(InfoCallIn $event)
    {
        $call = $event->call;
        $customerRepo = \App::make('Nh\Repositories\Customers\CustomerRepository');
        $leadRepo     = \App::make('Nh\Repositories\Leads\LeadRepository');
        if (is_null($customerRepo->checkExist(null, $call->from))
            && is_null($leadRepo->checkExist(null, $call->from))) {
            $leadRepo->store([
                'name'   => $call->from, 
                'phone'  => $call->from,
                'source' => \Nh\Repositories\Leads\Lead::PHONE_SOURCE
            ]);
        }
    }
}
