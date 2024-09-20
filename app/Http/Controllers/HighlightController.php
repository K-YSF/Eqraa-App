<?php

namespace App\Http\Controllers;

use App\Models\Highlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HighlightController extends Controller
{
    public function index()
    {
       return response()->json(auth()->user()->highlights,200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only(['book_id','content','page_number']),[
            'content'=>'required|string|min:3',
            'book_id'=>'required|exists:books,id',
            'page_number'=>'required|numeric',
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        $hl = Highlight::where([
            ['user_id','=',auth()->id()],
            ['book_id','=',$data['book_id']],
            ['page_number','=',$data['page_number']],
            ['content','=',$data['content']]
        ])->first();
        if(isset($hl))
            return response()->json(['message'=>'Invalid Action , Highlight Already Exist'],400);
        $highlight = Highlight::create([
            'user_id'=>auth()->id(),
            'book_id'=>$data['book_id'],
            'page_number'=>$data['page_number'],
            'content'=>$data['content'],
        ]);
        return response()->json($highlight,201);
    }
    public function show(string $id)
    {
        $highlight = Highlight::find($id);
        if(isset($highlight))
        {
            if($highlight->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
            return response($highlight,200);
        }
        else return response()->json(['message'=>'Highlight Not Found'],'404');
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->only(['content']),[
            'content'=>'string|min:3',
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        $highlight = Highlight::find($id);
        if(isset($highlight))
        {
            if($highlight->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
            $highlight->content = $data['content']??$highlight->content;
            $highlight->save();
            return response(['message'=>'Highlight Updated Successfully'],200);
        }
        else return response()->json(['message'=>'Highlight Not Found'],'404');
    }
    public function destroy(string $id)
    {
        $highlight = Highlight::find($id);
        if(isset($highlight))
        {
            if($highlight->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
                $highlight->delete();
                return response(['message'=>'Highlight Deleted Successfully'],200);
        }
        else return response()->json(['message'=>'Highlight Not Found'],'404');
    }
}
