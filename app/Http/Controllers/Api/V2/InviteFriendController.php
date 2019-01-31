<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Nh\Http\Requests\V2\InviteFriendRequest;
use Nh\Http\Controllers\Controller;
use Nh\Http\Resources\InviteFriend\InviteFriendResource;
use Nh\Models\InviteFriend;

class InviteFriendController extends Controller
{

    public function __construct()
    {
    }

    public function index(Request $request)
    {
        try{
            $phone = $request->phone;
            $data = ($phone) ? InviteFriend::wherePhoneOwner($phone)->orderBy('id', 'DESC')->limit(20)->get(): null;
            return new InviteFriendResource($data);
        }catch(\Exception $e){
            throw $e;
        }
    }

    public function store(InviteFriendRequest $request)
    {
        try{
            $inputs = $request->validated();
            $data = InviteFriend::create($inputs);

            return new InviteFriendResource($data);
        }catch(\Exception $e){
            throw $e;
        }
    }
}
