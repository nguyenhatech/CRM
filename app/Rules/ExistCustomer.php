<?php

namespace Nh\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;
use Nh\Models\InviteFriend;
use Nh\Repositories\Customers\Customer;

class ExistCustomer implements Rule
{
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
        return !(Customer::wherePhone($value)->exists());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
         // if passes false
        return 'SĐT đã là khách hàng của Havaz.';
    }
}

