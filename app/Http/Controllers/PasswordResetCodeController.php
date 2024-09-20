<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ForgetPasswordEmail;
use App\Models\PasswordResetCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetSuccessfully;
use Illuminate\Support\Facades\Validator;

class PasswordResetCodeController extends Controller
{
    public function forgotPassword(Request $request) {

        $validator = Validator::make($request->only(['email']), [
            'email'=>'email|required|exists:users,email'
        ]);

        if($validator->fails())
            return response()->json([$validator->errors()],400);

        $data = $validator->validated();

        PasswordResetCode::where('email',$data['email'])->delete();

        $data['code'] = rand(111111,999999);

        $passwordResetField = PasswordResetCode::create($data);

        Mail::to($passwordResetField->email)->send(new ForgetPasswordEmail($passwordResetField->code));

        return response()->json(["message"=>"Check Your Email For The Password Reset Code"] , 200);
    }
    public function checkPasswordResetCode(Request $request) {
        $validator = Validator::make($request->only(['code']),[
            'code'=>'required|min:6|max:6|string|exists:password_reset_codes,code',   
        ]);

        if($validator->fails())
            return response()->json([$validator->errors()],400);
        
        $data = $validator->validated();

        $passwordResetField = PasswordResetCode::where('code' , $data['code'])->first();
        if($passwordResetField->created_at <= now()->subMinutes(15))
        {
            $passwordResetField->delete();
            return response()->json(["message"=>"Expired Code"] , 422);
        }
        $passwordResetField['checked'] = true;
        return response()->json([
            'message'=>'The Code Is Valid',
            'code'=> $passwordResetField->code
        ] , 200);
    }
    public function passwordReset(Request $request) {
        $validator = Validator::make($request->only(['password','password_confirmation','code']), [
            'code'=>'required|min:6|max:6|string|exists:password_reset_codes,code',
            'password'=> 'required|min:8|max:48|confirmed',
        ]);
        if($validator->fails())
            return response()->json([$validator->errors()],400);
        $data = $validator->validated();
        $passwordResetField = PasswordResetCode::where('code' , $data['code'])->first();
        if(!$passwordResetField['checked'])
        {
            if($passwordResetField->created_at <= now()->subMinutes(15))
            {
                $passwordResetField->delete();
                return response()->json(["message"=>"Expired Code"] , 422);
            }
        }
        $user = User::where('email',$passwordResetField->email)->first();
        $user->update([
            'password'=> Hash::make($data['password'])
        ]);
        $passwordResetField->delete();
        Mail::to($user->email)->send(new PasswordResetSuccessfully());
        return  response()->json(['message'=>'Your Password Has Been Reset Successfully'] , 200);
    }

    
}
