<?php

namespace Nh\Rules\V2;

use Illuminate\Contracts\Validation\Rule;
use Nh\Repositories\Cgroups\Cgroup;

class CheckCustomerRated implements Rule
{
    protected $uuid = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($phone)
    {
        $this->phone = $phone;
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
        $name = strtoupper('REVIEW_'.$value.'_'.$this->phone);
        return !(Cgroup::whereName($name)->exists());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Booking này đã được đánh giá.';
    }
}
