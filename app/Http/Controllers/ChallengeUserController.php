<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeUser;

class ChallengeUserController extends Controller
{
    public function joinChallenge($id) {
        $challenge = Challenge::find($id);
        $user = auth()->user();
        if($user->is_admin)
            return response()->json(['message'=>'You Are Not Allowed To Perform These Actions [Join,Resign] In Reading Challenges'],200);
        else
        {
            if($challenge)
            {
                if($challenge->published)
                {
                    if($challenge->publishing_date > now()->subMinutes(10))
                    {
                        $challenge_user = ChallengeUser::where([['user_id','=',$user->id],['challenge_id','=',$challenge->id]])->first();
                        if(isset($challenge_user))
                        {
                            if($challenge_user->resigned)
                                return response()->json(['message'=>'You Have Resigned This Challenge , So You Can\'t Join Again'],200);
                            return response()->json(['message'=>'You Have Already Joined This Challenge'],200);
                        }
                        else
                        {
                            $user_books = $user->books()->pluck('book_id')->toArray();
                            $challenge_books = $challenge->books()->pluck('book_id')->toArray();
                            $joined_books = array_intersect($user_books,$challenge_books);
                            if(count($joined_books) > 0)
                                return response()->json(['message'=>'You Can Not Join This Challenge Because You Have Read One Or More Of The Books Involved In This Challenge'],400);
                            else
                            {
                                ChallengeUser::create([
                                    'user_id'=>$user->id,
                                    'challenge_id'=>$id
                                ]);
                                $challenge = Challenge::find($id); 
                                $challenge->save();
                                return response()->json(['message'=>$user->name.' Joined The '.$challenge->name.' Challenge Successfully']);
                            }
                        } 
                    }
                    else return response()->json(['message'=>'Sorry You\'re Too Late You Can\'t Join This Challenge Anymore Try To Be Faster Next Time'],400);
                }
                else return response()->json(['message'=>'You Can\'t Join This Challenge Yet Until It Get Published By The Admins'],400);
            }
            else return response()->json(['message'=>"There Is No Such A Challenge"],404);
        }
    }
    public function resignChallenge($id) {
        $challenge = Challenge::find($id);
        $user = auth()->user();
        if($user->is_admin)
            return response()->json(['message'=>'You Are Not Allowed To Perform These Actions [Join,Resign] In Reading Challenges'],200);
        else
        {
            if($challenge)
            {
                $challenge_user = ChallengeUser::where([['user_id','=',$user->id],['challenge_id','=',$challenge->id]])->first();
                if(isset($challenge_user))
                {
                    if(!$challenge_user->resigned)
                    {
                        $challenge_user->update([
                            'resigned'=>true
                        ]);
                        return response()->json(['message'=>$user->name . ' Resigned The '.$challenge->name.' Challenge Successfully'],200);
                    }
                    else
                        return response()->json(['message'=>'You Have Already Resigned From This Challenge'],200);
                }
                else return response()->json(['message'=>"You Did Not Join The ".$challenge->name.' Challenge'],400);
            }
            else return response()->json(['message'=>"There Is No Such A Challenge"],404);
        }
    }
}
