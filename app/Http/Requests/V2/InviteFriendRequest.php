<?php

namespace Nh\Http\Requests\V2;

use Nh\Http\Requests\ApiRequests;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Nh\Models\InviteFriend;
use Nh\Repositories\Customers\Customer;
use Nh\Rules\{
    LimitInviteFriend,
    ExistCustomer
};

class InviteFriendRequest extends ApiRequests
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_owner'=> ['required', 'phone', 'min:10', 'max:12', new LimitInviteFriend],
            'phone_friend' => ['required', 'phone', 'unique:invite_friends', 'min:10', 'max:12', 'different:phone_owner', new ExistCustomer]
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
}
