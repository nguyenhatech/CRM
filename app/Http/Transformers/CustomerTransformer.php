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
            'name'            => $customer->name,
            'email'           => $customer->email,
            'phone'           => $customer->phone,
            'home_phone'      => $customer->home_phone,
            'company_phone'   => $customer->company_phone,
            'fax'             => $customer->fax,
            'sex'             => $customer->sex,
            'sex_txt'         => $customer->sexText(),
            'facebook_id'     => $customer->facebook_id,
            'google_id'       => $customer->google_id,
            'website'         => $customer->website,
            'dob'             => $customer->dob,
            'job'             => $customer->job,
            'address'         => $customer->address,
            'company_address' => $customer->company_address,
            'source'          => $customer->source,
            'level'           => $customer->level,
            'level_txt'       => $customer->levelText(),
            'avatar'          => $customer->avatar,
            'created_at'      => $customer->created_at ? $customer->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'      => $customer->updated_at ? $customer->updated_at->format('d-m-Y H:i:s') : null
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