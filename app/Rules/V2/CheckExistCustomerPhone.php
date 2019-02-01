<?php

namespace Nh\Rules\V2;

use Illuminate\Contracts\Validation\Rule;
use Nh\Repositories\Customers\Customer;

class CheckExistCustomerPhone implements Rule
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
        return Customer::wherePhone($value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Khách hàng không tồn tại trong hệ thống CRM.';
    }
}
