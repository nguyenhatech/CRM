<?php

namespace Nh\Listeners;

use Nh\Events\NewCustomer;
use Nh\Jobs\SendingCutomerRegisterNew;
use Nh\Repositories\Promotions\DbPromotionRepository;
use Nh\Repositories\Cgroups\DbCgroupRepository;

class SendEmailNewCustomer
{
    protected $promotion;
    protected $cGroup;

    /**
     * Create the event listener.
     */
    public function __construct(DbPromotionRepository $promotion, DbCgroupRepository $cGroup)
    {
        $this->promotion = $promotion;
        $this->cGroup = $cGroup;
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
            //Id nhóm khách hàng
            $id_group_new_customer = env('ID_GROUP_NEW_CUSTOMER', null);

            if ($id_group_new_customer && intval($id_group_new_customer) > 0) {
                //Kiểm tra xem có chương trình khuyến mại dành cho khách hàng mới hay không?
                $promotion = $this->promotion->getPromotionByAccountNew($id_group_new_customer);

                // Kiểm tra thông tin nhóm khách hàng.
                $objGroup = $this->cGroup->getById($id_group_new_customer);
                if ($objGroup && $promotion) {
                    //Gửi tin nhắn và email email
                    $customer->groups()->attach($id_group_new_customer);
                    $job = new SendingCutomerRegisterNew($customer, $promotion);
                    dispatch($job)->onQueue(env('APP_NAME'));
                }
            }

        } catch (\Exception $e) {
        }
    }
}
