<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::all();
        return response()->json($complaints, 200);
    }
    public function show($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            return response()->json($complaint, 200);
        } else return response()->json(['message' => 'There Is No Complaint With Such Id'], 404);
    }
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->only(['content']),
            ['content' => 'required|string']
        );
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'content' => $validator->validated()['content']
        ]);
        return response()->json($complaint, 201);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->only(['content']),
            ['content' => 'string']
        );
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $complaint = Complaint::find($id);
        if ($complaint) {
            $complaint->update(['content' => $validator->validated()['content']]);
            return response()->json($complaint, 200);
        } else return response()->json(['message' => 'There Is No Complaint With Such Id'], 404);
    }
    public function destroy($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $complaint->delete();
            return response()->json(['message' => 'Complaint Deleted Successfully'], 200);
        } else return response()->json(['message' => 'There Is No Complaint With Such Id'], 404);
    }
}
