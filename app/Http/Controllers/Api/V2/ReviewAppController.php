<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Nh\Http\Requests\V2\CustomerReviewAppRequest;
use Nh\Http\Controllers\Controller;
use Nh\Http\Resources\InviteFriend\InviteFriendResource;
use Nh\Models\InviteFriend;

class ReviewAppController extends Controller
{

    public function __construct()
    {
    }

    public function store(CustomerReviewAppRequest $request)
    {
        try{
            $phone = $request->phone;
            $uuid = $request->uuid;
            event(new \Nh\Events\V2\CustomerReviewAppEvent($phone, $uuid));
            return response()->json([
                'status' => 200,
                'message' => 'Thanh cong',
            ]);
        }catch(\Exception $e){
            throw $e;
        }
    }
}
