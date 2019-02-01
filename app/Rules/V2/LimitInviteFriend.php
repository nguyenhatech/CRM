<?php

namespace Nh\Rules\V2;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;
use Nh\Models\InviteFriend;
use Nh\Repositories\Customers\Customer;

class LimitInviteFriend implements Rule
{
    const ALLOW_INVITE = 5;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (InviteFriend::wherePhoneOwner($value)->whereDate('created_at', Carbon::today())->count() <= self::ALLOW_INVITE);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Bạn bè giới thiệu đã quá số lượng cho phép ('.self::ALLOW_INVITE.' bạn/ngày.)';
    }
}