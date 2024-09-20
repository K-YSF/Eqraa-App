<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['is_admin'])->except(['index','show']);   
    }
    public function index()
    {
        $challenges = Challenge::all();
        foreach ($challenges as $challenge) {
            $challenge['challenge_books'] = $challenge->books()->get();
        }
        return response()->json($challenges,200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->only(['name','end_date']),
        [
            'name'=>'required|string|unique:challenges,name',
            'end_date'=>'required|date',
        ]);
        if($validator->fails())
            return response()->json($validator->errors(),400);
        else
        {
            $client_time_zone = $request->header('time_zone','Asia/Damascus');
            $data = $validator->validated();
            $end_date = Carbon::parse($data['end_date'] , $client_time_zone)->timezone(config('app.timezone'));
            if($end_date < now())
                return response()->json(['message'=>'Invalid End Date'],400);
            Challenge::create([
                'name'=>$data['name'],
                'end_date'=> $end_date
            ]);
            return response()->json(['message'=>'Challenge Created Successfully'],201);
        }
    }
    public function show(string $id)
    {
        $rc = Challenge::find($id);
        if($rc)
        {
            $rc['challenge_books'] = $rc->books()->get();
            return response()->json($rc,200);
        }
        else return response()->json(['message'=>"There Is No Such A Challenge"],404);
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->only(['name','end_date']),
        [
            'name'=>'string|unique:challenges,name',
            'end_date'=>'date',
        ]);
         if($validator->fails())
            return response()->json($validator->errors(),400);
        else
        {
            $client_time_zone = $request->header('time_zone','Asia/Damascus');
            $data = $validator->validated();
            $rc = Challenge::find($id);
            if($rc)
            {
                $end_date = $rc->end_date;
                if(isset($data['end_date']))
                {
                    $end_date = Carbon::parse($data['end_date'] , $client_time_zone)->timezone(config('app.timezone'));
                    if($end_date < now())
                        return response()->json(['message'=>'Invalid End Date'],400);
                }
                $rc->update([
                    'name' => $data['name']?? $rc->name,
                    'end_date' => $end_date
                ]);
                return response()->json(['message'=>"Challenge Updated Successfully"],200);
            }
            else return response()->json(['message'=>"There Is No Such A Challenge"],404);
        }
    }

    public function destroy(string $id)
    {
        $rc = Challenge::find($id);
        if($rc)
        {
            $rc->delete();
            return response()->json(['message'=>"Challenge Deleted Successfully"],200);
        }
        else return response()->json(['message'=>"There Is No Such A Challenge"],404);
    }
 
    public function publish($id) {
        $challenge = Challenge::find($id);
        if(isset($challenge))
        {
            if(count($challenge->books) > 0)
            {
                $challenge->published = true;
                $challenge->publishing_date = now();
                $challenge->save();
                return response()->json(['message'=>$challenge->name.' Challenge Has Been Published. Now Readers Can Joins And Start Reading'],200);
            }
            else return response()->json(['message'=>"You Can't Publish A Challenge Without Adding Any Book To It , Add At Least One Book"],400);
        }
        else return response()->json(['message'=>"There Is No Such A Challenge"],404);
    }
}
