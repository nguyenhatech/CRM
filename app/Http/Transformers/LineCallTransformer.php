<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\LineCalls\LineCall;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class LineCallTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'user'
    ];

    public function transform(LineCall $lineCall = null)
    {
        if (is_null($lineCall)) {
            return [];
        }

        $data = [
            'id'            => $lineCall->uuid,
            'vendor'        => $lineCall->vendor,
            'user_id'       => $lineCall->user ? $lineCall->user->uuid : '',
            'line'          => $lineCall->line,
            'phone_account' => $lineCall->phone_account,
            'email_acount'  => $lineCall->email_acount,
            'password'      => $lineCall->password,
            'profile_id'    => $lineCall->profile_id,
            'created_at'    => $lineCall->created_at ? $lineCall->created_at->format('d-m-Y H:i:s') : null,
            'updated_at'    => $lineCall->updated_at ? $lineCall->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeUser(LineCall $lineCall = null)
    {
        if (is_null($lineCall)) {
            return $this->null();
        }

        return $this->item($lineCall->user, new UserTransformer());
    }
}
