<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationCode;
use App\Mail\EmailVerifiedSuccessfully;
use Illuminate\Support\Facades\Validator;

class EmailVerificationCodeController extends Controller
{
    public function emailVerify(Request $request) {
        $validator = Validator::make($request->only(['code']),[
            'code'=>'string|required|min:6|max:6|exists:email_verification_codes,code'
        ]);
        if($validator->fails())
            return response()->json([$validator->errors()],400);
        $data = $validator->validated();
        $emailVerificationCode = EmailVerificationCode::where('code',$data['code'])->first();
        if($emailVerificationCode->created_at <= now()->subMinutes(15))
        {
            $emailVerificationCode->delete();
            return response()->json(["message"=>"Expired Code"] , 422);
        }
        $email = EmailVerificationCode::where('code',$data['code'])->first()->email;
        $user = User::where('email',$email)->first();
        $user->update([
            'email_verified_at'=>now()
        ]);
        Mail::to($user->email)->send(new EmailVerifiedSuccessfully());
        $emailVerificationCode->delete();
        $token = $user->createToken($user->name . '-' . 'AccessToken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ] , 200);
    }
    public function resendEmailVerificationCode(Request $request) {
        $validator = Validator::make($request->only(['email']), [
            'email'=>'email|required|exists:users,email'
        ]);
        if($validator->fails())
            return response()->json([$validator->errors()],400);
        $data = $validator->validated();
        EmailVerificationCode::where('email',$data['email'])->delete();
        $emailVerificationCode = EmailVerificationCode::create([
            'email' => $data['email'],
            'code' => rand(111111,999999)
        ]);
        $user = User::where('email',$data['email'])->first();
        Mail::to($data['email'])->send(new EmailVerification($user->name , $emailVerificationCode->code));
        return response()->json([
            "message" => "Check Your Email For The Email Verification Code"
        ] , 200);
    }
}
