<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookUser;
use Illuminate\Http\Request;
use App\Models\BookChallenge;
use App\Models\ChallengeUser;
use Illuminate\Support\Facades\Validator;

class BookUserController extends Controller
{
    public function rate(Request $request,$id) {
        $validator = Validator::make($request->only(['rating']),['rating'=>'required|numeric|max:5|min:0']);
        if($validator->fails())
            return response()->json([$validator->errors()],400);
        else
        {
            $validRating = $validator->validated()['rating'];
            $book = Book::find($id);
            if($book)
            {
                BookUser::updateOrCreate([
                    'user_id'=>auth()->id(),
                    'book_id'=>$id
                ],['rating'=>$validRating]);
                $ratings = BookUser::where([['book_id','=',$id],['rating','<>','-1']])->pluck('rating');
                $sum = 0;
                foreach($ratings as $rating):
                    $sum += $rating;
                endforeach;
                $book->rating = $sum/count($ratings);
                $book->save();
                return response()->json(['message'=>'You Rated '.$book->title.' As '.$validRating . ' Star'],200);
            }
            else
                return response()->json(['message'=>'There Is No Such A Book'],404);
        }
    }
    public function myFavorite() {
        $user_favorite_ids = BookUser::where([['user_id','=',auth()->id()],['favorite','=','1']])->pluck('book_id');
        $user_favorite_books = Book::whereIn('id',$user_favorite_ids)->get();
        return response()->json($user_favorite_books,200);
    }
    public function addToFavorite($id) {
        $book = Book::find($id);
        if($book)
        {
            BookUser::updateOrCreate([
                'user_id'=>auth()->id(),
                'book_id'=>$id
            ],['favorite'=>true]);
            return response()->json(['message'=>'You Added '.$book->title.' To Your Favorite'],200);
        }
        else
            return response()->json(['message'=>'There Is No Such A Book'],404);
    }
    public function removeFromFavorite($id) {
        $book = Book::find($id);
        if($book)
        {
            $book_user = BookUser::where([['user_id','=',auth()->id()],['book_id','=',$id]])->first();
            $book_user->favorite = false;
            $book_user->save();
            return response()->json(['message'=>'You Removed '.$book->title.' From Your Favorite'],200);
        }
        else
            return response()->json(['message'=>'There Is No Such A Book'],404);
    }

   public function read(Request $request,$id)  {
        $book = Book::find($id);
        if(isset($book))
        {
            $validator = Validator::make($request->only(['page_number']),[
                'page_number' => 'required|numeric'
            ]);
            if($validator->fails())
                return response()->json($validator->errors(),400);
            $page_number = $validator->validated()['page_number'];
            if($page_number > $book->number_of_pages || $page_number < 0)
                return response()->json(['message'=>'The Page Number Is Out Of The Range Of The Book Pages'],400);
            // Calculating The User Progress In This Book :
            BookUser::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'book_id' => $id
                ]
                ,
                [
                    'percentage' => round(($page_number/$book->number_of_pages)*100,2)
                ]
            );
            // Calculating The User Progress If The Read Book Was Involved In A Challenge :
            $userJoinedChallengesIds = auth()->user()->challenges()->pluck('challenge_id');
            $challengesTheUserJoinedAndHaveTheReadBookIds = BookChallenge::whereIn('challenge_id',$userJoinedChallengesIds)->where('book_id','=',$id)->pluck('challenge_id');
            if(isset($challengesTheUserJoinedAndHaveTheReadBookIds))
            {
                foreach($challengesTheUserJoinedAndHaveTheReadBookIds as $challengeId)
                {
                    $challengeUser = ChallengeUser::where([['user_id','=',auth()->id()],['challenge_id',$challengeId]])->first();
                    $challengeBooks = BookChallenge::where('challenge_id','=',$challengeId)->get();
                    $total = 0;
                    foreach($challengeBooks as $challengeBook)
                    {
                        $bu = BookUser::where([['user_id','=',auth()->id()],['book_id','=',$challengeBook->id]])->first();
                        $total = $total + $bu->percentage;
                    }
                    $challengeUser->update([
                        'progress' => round(($total/(count($challengeBooks)*100))*100,2)
                    ]);
                }
            }
           return response()->json(['message'=>'You Have Read The Book'],200);
        }
        else return response()->json(['message'=>'There Is No Such A Book'],404);
   }
}
