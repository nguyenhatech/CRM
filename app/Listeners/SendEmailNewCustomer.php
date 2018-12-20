<?php

namespace Nh\Listeners;

use Nh\Events\NewCustomer;
use Nh\Jobs\SendingCutomerRegisterNew;
use Nh\Repositories\Promotions\DbPromotionRepository;

class SendEmailNewCustomer
{
    protected $promotion;

    /**
     * Create the event listener.
     */
    public function __construct(DbPromotionRepository $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Handle the event.
     *
     * @param NewCustomer $event
     */
    public function handle(NewCustomer $event)
    {
        $customer = $event->customer;

        try {
            $job = new SendingCutomerRegisterNew($customer, $this->promotion);
            dispatch($job)->onQueue(env('APP_NAME'));
        } catch (\Exception $e) {
        }
    }
}
