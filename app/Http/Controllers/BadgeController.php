<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BadgeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['is_admin'])->except(['show','index']);   
    }
    public function index(Request $request) {
        $validator = Validator::make($request->only(['challenge_id']),[
            'challenge_id'=>'required|exists:challenges,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $id = $validator->validated()['challenge_id'];
        $badges = Badge::where('challenge_id','=',$id)->get();
        return response()->json($badges,200);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->only(['name','details','challenge_id']),[
            'name'=>'required|unique:badges,name|string|min:3',
            'details'=>'required|string',
            'challenge_id'=>'required|exists:challenges,id'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        $badge = Badge::create([
            'name' => $data['name'],
            'details' => $data['details'],
            'challenge_id' => $data['challenge_id'],
        ]);
        return response()->json($badge,201);
    }
    public function update(Request $request,$id) {
        $badge = Badge::find($id);
        if(isset($badge))
        {
            $validator = Validator::make($request->only(['name','details','challenge_id']),[
                'name'=>'unique:badges,name|string|min:3',
                'details'=>'string',
                'challenge_id'=>'exists:challenges,id'
            ]);
            if($validator->fails())
                return response()->json($validator->errors(),400);
            $data = $validator->validated();
            $badge->name =$data['name'] ?? $badge->name;
            $badge->details =$data['details'] ?? $badge->details;
            $badge->challenge_id =$data['challenge_id'] ?? $badge->challenge_id;
            $badge->save();
            return response()->json($badge);
        }
        else return response()->json(['message'=>'There Is No Such A Badge'],404);
    }
    public function destroy($id) {
        $badge = Badge::find($id);
        if(isset($badge))
        {
            $badge->delete();
            return response()->json(['message'=>'Badge Removed Successfully'],200);
        }
        else return response()->json(['message'=>'There Is No Such A Badge'],404);
    }
    public function show($id)  {
        $badge = Badge::find($id);
        if(isset($badge))
            return response()->json($badge,200);
        else return response()->json(['message'=>'There Is No Such A Badge'],404);
    }
}
