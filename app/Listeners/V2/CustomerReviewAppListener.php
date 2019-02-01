<?php

namespace Nh\Listeners\V2;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nh\Events\V2\CustomerReviewAppEvent;

use Nh\Repositories\Cgroups\Cgroup;
use Nh\Repositories\Promotions\Promotion;
use Nh\Repositories\Customers\Customer;
use Nh\Repositories\CustomerCgroups\CustomerCgroup;
use Nh\Models\InviteFriend;
use Carbon\Carbon;

class CustomerReviewAppListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    protected $moneyApplied= 0;
    protected $dayApplied= 0;

    public function __construct()
    {
        $this->moneyApplied= env('REIVIEW_APP_OF_CUSTOMER_MONEY_APPLIED');
        $this->dayApplied= env('REIVIEW_APP_OF_CUSTOMER_DAY_APPLIED');
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CustomerReviewAppEvent $event)
    {
        $conditionInputs = ($this->moneyApplied && $this->dayApplied && (int)$this->moneyApplied  > 0 &&  (int)$this->dayApplied > 0);

        if (!$conditionInputs) return false;

        $phone = $event->phone;
        $uuid = $event->uuid;

        $customer = $this->getInfoCustomer($phone);
        $dataCgroup = ($customer instanceof Customer) ? $this->createCgroup($customer, $uuid) : null;
        $dataPromotions = ($dataCgroup instanceof Cgroup)? $this->createPromotion($dataCgroup): null;

        if ($dataPromotions instanceof Promotion) {
            // Đẩy số người này nhóm
            $status = $this->putCustomerToGroup($dataCgroup, $customer);
            // Gửi tin nhắn cho khách hàng đã đánh giá
            $job = new \Nh\Jobs\V2\SendSmsToCustomerReviewApp($customer, $dataPromotions->code, $this->dayApplied, $this->moneyApplied, $uuid);
            dispatch($job)->onQueue(env('APP_NAME'));
        }
    }

    private function getInfoCustomer($phone){
        return Customer::wherePhone($phone)->first();
    }

    private function createCgroup (Customer $customer, $uuid){
        $phone = $customer->phone;

        // Kiểm tra xem khác 
        $inputsGroup = [
            'name' => strtoupper('REVIEW_'.$uuid.'_'.$phone),
            'type' => 1,
            'description' => 'Chương trình ưu đãi cho khách hàng đánh giá app.',
            'client_id' => 3
        ];

        return Cgroup::create($inputsGroup);
    }

    private function createPromotion(Cgroup $dataCgroup) {
        $code = $this->genCodePromotion();
        $start = Carbon::today();
        $end = Carbon::today()->addDays($this->dayApplied);

        $inputsPromotions = [
            'amount'=> $this->moneyApplied,
            'amount_segment'=>$this->moneyApplied,
            'cgroup_id' => $dataCgroup->id,
            'code'=> $code,
            'content'=> 'Chương trình ưu đãi cho khách hàng đánh giá app.',
            'date_end'=> $end,
            'date_start'=> $start,
            'description'=> 'Chương trình ưu đãi cho khách hàng đánh giá app.',
            'limit_time_type' => 2,
            'quantity' => 1,
            'quantity_per_user' => 1,
            'sms_template' => 'Chương trình ưu đãi cho khách hàng đánh giá app.',
            'target_type' => 0,
            'time_end' =>'23:59',
            'time_start'=> '00:00',
            'title'=> 'Chương trình ưu đãi cho khách hàng đánh giá app.',
            'type'=> 0,
            'client_id'=> 3
        ];

        return Promotion::create($inputsPromotions);
    }

    private function putCustomerToGroup(Cgroup $cgroup, Customer $customer ){
        return $customer->groups()->sync((array)$cgroup->id);
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
