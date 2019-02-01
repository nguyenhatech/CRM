<?php

namespace Nh\Http\Requests\V2;

use Nh\Http\Requests\ApiRequests;
use Nh\Rules\V2\{
    CheckExistCustomerPhone,
    CheckCustomerRated
};

class CustomerReviewAppRequest extends ApiRequests
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $phone = request()->phone;

        return [
            'uuid'=> ['required', 'string', new CheckCustomerRated($phone)],
            'phone'=> ['required', 'phone', 'min:10', 'max:12', new CheckExistCustomerPhone]
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Thông tin bắt buộc.',
            'phone.required' => 'Thông tin bắt buộc.',
            'phone.phone' => 'Định dạng không đúng.'
        ];
    }
}
