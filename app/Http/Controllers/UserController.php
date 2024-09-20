<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified'])->except(['signUp', 'signIn']);
    }
    public function users(Request $request)
    {
        $users = User::filters($request->query('search'))->get();
        return response()->json($users, 200);
    }
    public function deleteAccount($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($id == auth()->id()) {
                $user->delete();
                return response()->json(['Your Account Has Been Deleted'], 200);
            } else
                return response()->json(['message' => 'Unauthorized Action'], 403);
        } else
            return response()->json(['message' => 'User Not Found'], 404);
    }
    public function profile($id)
    {
        $user = User::find($id);
        if (isset($user))
            return response()->json([
                'user' => $user,
                'user_books' => $user->books()->get(),
                'user_challenges' => $user->challenges()->get(),
                'user_badges' => $user->badges()->get(),
                'user_highlights' => $user->highlights()->get(),
                'user_bookmarks' => $user->bookmarks()->get()
            ], 200);
        return response()->json(['message' => 'User Not Found'], 404);
    }
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            if ($id == auth()->id()) {
                $validator = Validator::make($request->only(['name', 'avatar', 'bio', 'social_links']), [
                    'name' => 'string|min:3|max:48',
                    'bio' => 'string',
                    'social_links' => 'array',
                    'social_links.*' => 'url',
                    'avatar' => 'file|mimes:png,jpg',
                ]);
                if ($validator->fails())
                    return response()->json($validator->errors(), 400);
                $data = $validator->validated();
                $user->name = $data['name'] ?? $user->name;
                $user->bio = $data['bio'] ?? $user->bio;
                $user->social_links = array_merge([], $data['social_links'] ?? []);
                $user->social_links = array_unique($user->social_links);
                if (isset($data['avatar'])) {
                    $avatar_name = str_replace(' ', '', $user->name) . $user->id . '.' . $data['avatar']->getClientOriginalExtension();
                    Storage::disk('public')->delete('Avatars/' . basename($user->avatar));
                    $avatar_path = $data['avatar']->storeAs('Avatars', $avatar_name, 'public');
                    $user->avatar = $avatar_path;
                }
                $user->save();
                return response()->json([
                    'user' => $user,
                    'user_books' => $user->books()->get(),
                    'user_challenges' => $user->challenges()->get()
                ], 200);
            } else
                return response()->json(['message' => 'Unauthorized Action'], 403);
        }
        return response()->json(['message' => 'User Not Found'], 404);
    }
    public function signUp(Request $request)
    {
        $validator = Validator::make(
            $request->only(['name', 'email', 'password', 'is_admin']),
            [
                'name' => 'required|string|min:3|max:48',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:48',
                'is_admin' => 'boolean'
            ]
        );
        if ($validator->fails())
            return response()->json([
                $validator->errors()
            ], 400);
        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        EmailVerificationCode::where('email', $user->email)->delete();
        $emailVerificationCode = EmailVerificationCode::create([
            'email' => $user->email,
            'code' => rand(111111, 999999)
        ]);
        Mail::to($user->email)->send(new EmailVerification($user->name, $emailVerificationCode->code));
        return response()->json([
            "message" => "Check Your Email For The Email Verification Code"
        ], 201);
    }
    public function signIn(Request $request)
    {
        $validator = Validator::make(
            $request->only(['email', 'password']),
            [
                'email' => 'required|email',
                'password' => 'required|min:8|max:48'
            ]
        );
        if ($validator->fails())
            return response()->json([
                $validator->errors()
            ], 400);
        $data = $validator->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password))
            return response()->json([
                "message" => "Invalid Credentials"
            ], 400);
        $token = $user->createToken($user->name . '-' . 'AccessToken')->plainTextToken;
        return response()->json([
            "user" => $user,
            "token" => $token
        ], 200);
    }
    public function signOut(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(["message" => 'Logged Out Successfully'], 200);
    }
}
