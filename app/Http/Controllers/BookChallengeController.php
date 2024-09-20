<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Challenge;
use Illuminate\Http\Request;
use App\Models\BookChallenge;
use Illuminate\Support\Facades\Validator;

class BookChallengeController extends Controller
{
    
   public function addToChallenge(Request $request, $id) {
    $book = Book::find($id);
    if(isset($book))
    {
        $validator = Validator::make($request->only(['challenge_id']),[
            'challenge_id' => 'required|exists:challenges,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $challenge_id = $validator->validated()['challenge_id'];
        $challenge = Challenge::find($challenge_id);
        $bc = BookChallenge::where('challenge_id',$challenge_id)->where('book_id',$id)->first();
        if(isset($bc))
            return response()->json(['message'=>'You Have Already Added This Book To This Challenge'],400);
        BookChallenge::create([
            'book_id' => $id,
            'challenge_id'=>$challenge_id
        ]);
        return response()->json(['message'=>'You Added '.$book->title.' Book To The '.$challenge->name.' Challenge'],200);
    }
    else return response()->json(['message'=>'There Is No Such A Book'],404);
   }
   public function removeFromChallenge(Request $request , $id) {
    $book = Book::find($id);
    if(isset($book))
    {
        $validator = Validator::make($request->only(['challenge_id']),[
            'challenge_id' => 'required|exists:challenges,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $challenge_id = $validator->validated()['challenge_id'];
        $challenge = Challenge::find($challenge_id);
        $bc = BookChallenge::where('challenge_id',$challenge_id)->where('book_id',$id)->first();
        if(isset($bc))
        {
            $bc->delete();
            return response()->json(['message'=>'You Removed '.$book->title.' Book From The '.$challenge->name.' Challenge'],200);
        }
        else
            return response()->json(['message'=>'The '.$challenge->name.' Challenge Does Not Include This Book'],400);
    }
    else return response()->json(['message'=>'There Is No Such A Book'],404);
   }
}
