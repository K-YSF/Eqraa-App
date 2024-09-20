<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookmarkController extends Controller
{
    public function index()
    {
       return response()->json(auth()->user()->bookmarks,200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only(['book_id','name','note','page_number']),[
            'name'=>'required|string|min:3',
            'note'=>'string',
            'book_id'=>'required|exists:books,id',
            'page_number'=>'required|numeric'
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        if($data['page_number'] < 0 || $data['page_number'] > Book::find($data['book_id'])->number_of_pages)
            return response()->json(['message'=>'Page Number Out Of Book Range Of Pages'],400);
        $bm = Bookmark::where([['page_number','=',$data['page_number']],['user_id','=',auth()->id()],['book_id','=',$data['book_id']]])->first();
        if(isset($bm))
            return response()->json(['message'=>'Invalid Action , Bookmark Already Exist'],400);
        $bookmark = Bookmark::create([
            'user_id' => auth()->id(),
            'name'=>$data['name'],
            'note'=>$data['note'], 
            'page_number'=>$data['page_number'], 
            'book_id'=>$data['book_id'], 
        ]);
        return response()->json($bookmark,201);
    }
    public function show(string $id)
    {
        $bookmark = Bookmark::find($id);
        if(isset($bookmark))
        {
            if($bookmark->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
            return response($bookmark,200);
        }
        else return response()->json(['message'=>'Bookmark Not Found'],'404');
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->only(['name','note']),[
            'name'=>'string|min:3',
            'note'=>'string',
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        $data = $validator->validated();
        $bookmark = Bookmark::find($id);
        if(isset($bookmark))
        {
            if($bookmark->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
            $bookmark->update([
                'name'=>$data['name']??$bookmark->name,
                'note'=>$data['note']??$bookmark->note
            ]);
            return response(['message'=>'Bookmark Updated Successfully','bookmark'=>$bookmark],200);
        }
        else return response()->json(['message'=>'Bookmark Not Found'],'404');
    }
    public function destroy(string $id)
    {
        $bookmark = Bookmark::find($id);
        if(isset($bookmark))
        {
            if($bookmark->user_id != auth()->id())
                return response()->json(['message'=>'Unauthorized Action'],403);
            $bookmark->delete();
            return response(['message'=>'Bookmark Deleted Successfully'],200);
        }
        else return response()->json(['message'=>'Bookmark Not Found'],'404');
    }
}
