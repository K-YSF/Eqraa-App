<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendshipController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','verified']);
    }
    public function sendFriendRequest(Request $request) {
        $validator = Validator::make($request->only(['id']),[
            'id'=>'required|numeric|exists:users,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $id = $validator->validated()['id'];
        $friend = User::find($id);
        if($id == auth()->id())
            return response()->json(['message'=>'You Can\'t Send A Friend Request To Yourself'],400);
        if(isset($friend))
        {
            $friendship = Friendship::where([['user_id','=',auth()->id()],['friend_id','=',$id]])->first();
            if(isset($friendship))
                return response()->json(['message'=>'You Have Already Sent A Friend Request To '.$friend->name],400);
            else
            {
                Friendship::create([
                    'user_id' => auth()->id(),
                    'friend_id'=>$id,
                    'accepted' => 0
                ]);
                return response()->json(['message'=>'You Have Sent A Friend Request To '.$friend->name],201);
            }
        }
        else return response()->json(['message'=>'User Not Found !'] , 404);
    }
    public function cancelFriendRequest(Request $request) {
        $validator = Validator::make($request->only(['id']),[
            'id'=>'required|numeric|exists:users,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $id = $validator->validated()['id'];
        $friend = User::find($id);
        if(isset($friend))
        {
            $friendship = Friendship::where([['user_id','=',auth()->id()],['friend_id','=',$id]])->first();
            if(isset($friendship))
            {
                if($friendship->accepted)
                    return response()->json(['message'=>$friend->name . ' Has Accepted Your Friend Request , You Can\'t Cancel The Request Anymore , But You Can Remove Them From Your Friends'] , 400);
                else
                {
                    $friendship->delete();
                    return response()->json(['message'=>'You Have Canceled The Friend Request You\'ve Sent To '.$friend->name] , 200);
                }
            }
            else
                return response()->json(['message'=>'You Did not Send A Friend Request To '.$friend->name] , 400);
        }
        else return response()->json(['message'=>'User Not Found !'] , 404);
    }
    public function receivedFriendRequests() {
        $receivedFriendRequests = Friendship::where('friend_id','=',auth()->id())->where('accepted','=',0)->get();
        return response()->json($receivedFriendRequests, 200);
    }
    public function sentFriendRequests() {
        $sentFriendRequests = Friendship::where('user_id','=',auth()->id())->get();
        return response()->json($sentFriendRequests, 200);
    }
    public function processFriendRequest(Request $request) {
        $validator = Validator::make($request->only(['id','res']),[
            'id'=>'required|numeric|exists:friendships,id',
            'res'=>'required|boolean'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        $friendship = Friendship::find($data['id']);
        if(auth()->id() == $friendship->friend_id)
        {
            $friendship->update([
                'accepted' => $data['res']
            ]);
            $friend = User::find($friendship->user_id);
            if($data['res'])
            {
                $user = User::find($friendship->friend_id);
                $user->update([
                    'number_of_friends' => $user->number_of_friends + 1
                ]);
                $friend->update([
                    'number_of_friends' => $friend->number_of_friends + 1
                ]);
                Friendship::create([
                    'user_id' => $friendship->friend_id,
                    'friend_id' => $friendship->user_id,
                    'accepted' => 1
                ]);
                return response()->json(['message'=>'You And '.$friend->name.' Are Now Friends On Eqraa'],200);
            }
            else
            {
                $friendship->delete();
                return response()->json(['message'=>'You Refused '.$friend->name.' Friend Request'],200);
            }
            
        }
        else return response()->json(['Unauthorized Action'],403);
    }
    public function myFriends() {
        $friends = auth()->user()->friends;
        return response()->json($friends,200);
    }
    public function removeFriend($id) {
        $friend = User::find($id);
        if(isset($friend))
        {
            $friendship1 = Friendship::where('user_id','=',auth()->id())->where('friend_id','=',$id)->first(); 
            $friendship2 = Friendship::where('user_id','=',$id)->where('friend_id','=',auth()->id())->first();
            if(isset($friendship1) & isset($friendship2))
            {
                $friendship1->delete();
                $friendship2->delete();
                $friend->number_of_friends = $friend->number_of_friends - 1;
                $friend->save();
                auth()->user()->number_of_friends = auth()->user()->number_of_friends - 1;
                auth()->user()->save(); 
                return response()->json(['message'=>'You And '.$friend->name.' Are No Longer Friends'],200);
            }
            else
                return response()->json(['message'=>'You Are Not Friend With '.$friend->name],400);
        }
        else return response()->json(['message'=>'User Not Found !'] , 404);
    }
}
