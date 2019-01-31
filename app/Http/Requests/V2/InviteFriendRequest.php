<?php

namespace Nh\Http\Requests\V2;

use Nh\Http\Requests\ApiRequests;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Nh\Models\InviteFriend;
use Nh\Repositories\Customers\Customer;

class InviteFriendRequest extends ApiRequests
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $phone_owner = request()->phone_owner;
        $phone_friend = request()->phone_friend;

        //dd($phone_owner);
        return [
            'phone_owner'=> ['required', 'phone', 'min:10', 'max:12'],
            'phone_friend' => ['required', 'phone', 'unique:invite_friends', 'min:10', 'max:12', 'different:phone_owner']
        ];
    }

    public function messages()
    {
        return [
            'phone_owner.required' => 'Thông tin bắt buộc.',
            'phone_friend.required'  => 'Thông tin bắt buộc.',
            'phone_friend.different'  => 'Lỗi: Bạn đang tự giới thiệu bản thân.',
            'phone_owner.phone' => 'Định dạng không đúng.',
            'phone_friend.phone'  => 'Định dạng không đúng.',
            'phone_friend.unique'  => 'Người bạn này đã được khách hàng khác giới thiệu.',
        ];
    }

    protected function getNumberInvited($phone_owner)
    {
        $today = Carbon::today();
        return InviteFriend::wherePhoneOwner($phone_owner)->count();
    }

    protected function checkIsCustomer($phone_friend) {
        return Customer::wherePhone($phone_friend)->exists();
    }
}
