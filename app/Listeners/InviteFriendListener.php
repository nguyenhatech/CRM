<?php

namespace Nh\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nh\Events\NewCustomer;
use Nh\Repositories\Cgroups\Cgroup;
use Nh\Repositories\Promotions\Promotion;
use Nh\Repositories\Customers\Customer;
use Nh\Repositories\CustomerCgroups\CustomerCgroup;
use Nh\Models\InviteFriend;
use Carbon\Carbon;

class InviteFriendListener
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
    public function handle(NewCustomer $event)
    {
        //dd($code, $num);
        $moneyApplied= env('INVITE_FRIEND_MONEY_APPLIED');
        $dayApplied= env('INVITE_FRIEND_DAY_APPLIED');

        $conditionInputs = ($moneyApplied && $dayApplied && (int)$moneyApplied  > 0 &&  (int)$dayApplied > 0);

        if (!$conditionInputs) return false;

        $customerNew = $event->customer;
        $customerFriend = ($customerNew instanceof Customer) ? $this->getCustomerFriend($customerNew): null;
        $customerOwner = ($customerFriend instanceof InviteFriend)? $this->getCustomerOwner($customerFriend): null;
        $dataCgroup = ($customerNew instanceof Customer && $customerOwner instanceof Customer) ? $this->createCgroup($customerNew, $customerOwner ) : null;
        $dataPromotions = ($dataCgroup instanceof Cgroup)? $this->createPromotion($dataCgroup): null;

        if ($dataPromotions instanceof Promotion && $customerFriend instanceof InviteFriend) {
            $customerFriend->is_customer = true;
            $customerFriend->save();

            // Đẩy số người này nhóm
            $this->putCustomerToGroup($dataCgroup, $customerOwner);

            // Gửi tin nhắn cho khách hàng owner
            $job = new \Nh\Jobs\SendSmsToPresenter($customerFriend, $dataPromotions->code, $dayApplied, $moneyApplied);
            dispatch($job)->onQueue(env('APP_NAME'));
        }
    }

    private function getCustomerFriend (Customer $customerNew){
        $phoneFriend = $customerNew->phone;
        return $customerFriend = InviteFriend::whereIsCustomer(false)->wherePhoneFriend($phoneFriend)->first();
    }

    private function getCustomerOwner (InviteFriend $customerFriend){
        $phoneOwner = $customerFriend->phone_owner;
        return $customerOwner = Customer::wherePhone($phoneOwner)->first();
    }

    private function createCgroup (Customer $customerNew,  Customer $customerOwner){
        $phoneFriend = $customerNew->phone;
        $phoneOwner = $customerOwner->phone;

        $inputsGroup = [
            'name' => 'INVITE_'.$phoneFriend.'_'.$phoneOwner,
            'type' => 1,
            'description' => 'Chương trình giới thiệu bạn bè.',
            'client_id' => 3
        ];
        return Cgroup::create($inputsGroup);
    }

    private function createPromotion(Cgroup $dataCgroup) {
        $moneyApplied= env('INVITE_FRIEND_MONEY_APPLIED');
        $dayApplied= env('INVITE_FRIEND_DAY_APPLIED');
        $code = $this->genCodePromotion();
        $start = Carbon::today();
        $end = Carbon::today()->addDays($dayApplied);

        $inputsPromotions = [
            'amount'=> $moneyApplied,
            'amount_segment'=>$moneyApplied,
            'cgroup_id' => $dataCgroup->id,
            'code'=> $code,
            'content'=> 'Chương trình khuyến mại giới thiệu bạn bè.',
            'date_end'=> $end,
            'date_start'=> $start,
            'description'=> 'Chương trình khuyến mại giới thiệu bạn bè.',
            'limit_time_type' => 2,
            'quantity' => 1,
            'quantity_per_user' => 1,
            'sms_template' => 'Chuong trinh khuyen mai gioi thieu ban be.',
            'target_type' => 0,
            'time_end' =>'23:59',
            'time_start'=> '00:00',
            'title'=> 'Chương trình khuyến mại giới thiệu bạn bè',
            'type'=> 0,
            'client_id'=> 3
        ];

        return Promotion::create($inputsPromotions);
    }

    private function putCustomerToGroup(Cgroup $cgroup, Customer $customer ){

        $input = [
            'customer_id' => $customer->id,
            'cgroup_id' => $cgroup->id,
        ];

        return CustomerCgroup::create($input);
    }

    private function genCodePromotion(){
        $code = $this->genCode();
        while (Promotion::whereCode($code)->exists()) 
        {
            $char= randStrGen(3);
            $num = rand(100, 999);
            $code = $char.$num;
        }

        return strtoupper($code);
    }

    private function genCode(){ 
        return randStrGen(3).rand(100, 999);
    }
}
