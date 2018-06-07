<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\Customers\Customer;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CustomerTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        // 'roles'
    ];

     public function transform(Customer $customer = null)
    {
        if (is_null($customer)) {
            return [];
        }

        $data = [
            'id'              => $customer->uuid,
            'name'            => $customer->name ? $customer->name : $customer->email,
            'email'           => $customer->email,
            'phone'           => $customer->phone,
            'home_phone'      => $customer->home_phone,
            'company_phone'   => $customer->company_phone,
            'fax'             => $customer->fax,
            'sex'             => $customer->sex,
            'sex_txt'         => $customer->sexText(),
            'identification_number' => $customer->identification_number,
            'facebook_id'     => $customer->facebook_id,
            'google_id'       => $customer->google_id,
            'website'         => $customer->website,
            'dob'             => $customer->dob ? $customer->dob->format('Y-m-d') : null,
            'job'             => (int) $customer->job,
            'address'         => $customer->address,
            'city_id'         => (int) $customer->city_id,
            'company_address' => $customer->company_address,
            'source'          => $customer->source,
            'level'           => $customer->level,
            'level_txt'       => $customer->levelText(),
            'avatar'          => $customer->avatar,
            'avatar_path'     => $customer->getAvatar(),
            'total_amount'    => abs($customer->getTotalAmount()),
            'total_point'     => $customer->getTotalPoint(),
            'total_payment'   => $customer->payments->count(),
            'created_at'      => $customer->created_at ? $customer->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'      => $customer->updated_at ? $customer->updated_at->format('d-m-Y H:i:s') : null,
            'last_payment'      => $customer->last_payment ? $customer->last_payment->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    // public function includeRoles(Customer $customer = null)
    // {
    //     if (is_null($customer)) {
    //         return $this->null();
    //     }

    //     return $this->collection($customer->roles, new RoleTransformer());
    // }
}
