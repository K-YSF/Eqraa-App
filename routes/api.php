<?php

use App\Models\Complaint;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookUserController;
use App\Http\Controllers\BadgeUserController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\HighlightController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\BookChallengeController;
use App\Http\Controllers\ChallengeUserController;
use App\Http\Controllers\PasswordResetCodeController;
use App\Http\Controllers\EmailVerificationCodeController;

// By Kheder Youssef 💜
Route::prefix('users/')->group(function () {
       Route::controller(UserController::class)->group(function () {
              Route::get('{id}', 'profile');
              Route::put('{id}', 'updateProfile');
              Route::delete('{id}', 'deleteAccount');
       });
       Route::controller(UserController::class)->group(function () {
              Route::get('', 'users');
       });
       Route::prefix('auth/')->group(function () {
              Route::controller(UserController::class)->group(function () {
                     Route::post('sign-out', 'signOut');
                     Route::post('sign-up', 'signUp');
                     Route::post('sign-in', 'signIn');
              });
              Route::controller(PasswordResetCodeController::class)->group(function () {
                     Route::post('forgot-password', 'forgotPassword');
                     Route::post('check-password-reset-code', 'checkPasswordResetCode');
                     Route::post('password-reset', 'passwordReset');
              });
              Route::controller(EmailVerificationCodeController::class)->group(function () {
                     Route::post('email-verify', 'emailVerify');
                     Route::post('resend-email-verification-code', 'resendEmailVerificationCode');
              });
       });
       Route::prefix('friendship/')->controller(FriendshipController::class)->group(function () {
              Route::post('send-friend-request', 'sendFriendRequest');
              Route::delete('cancel-friend-request', 'cancelFriendRequest');
              Route::get('sent-friend-requests', 'sentFriendRequests');
              Route::get('received-friend-requests', 'receivedFriendRequests');
              Route::post('process-friend-request', 'processFriendRequest');
              Route::get('my-friends', 'myFriends');
              Route::delete('my-friends/{id}', 'removeFriend');
       });
});

Route::prefix('books/')->middleware(['auth:sanctum', 'verified'])->group(function () {
       Route::controller(BookUserController::class)->group(function () {
              Route::get('my-favorite', 'myFavorite');
       });
       Route::controller(BookController::class)->group(function () {
              Route::post('', 'store');
              Route::get('', 'index');
              Route::get('{id}', 'show');
              Route::delete('{id}', 'destroy');
              Route::put('{id}', 'update');
              Route::get('{id}/download', 'download');
       });
       Route::controller(BookUserController::class)->group(function () {
              Route::post('{id}/add-to-favorite', 'addToFavorite');
              Route::delete('{id}/remove-from-favorite', 'removeFromFavorite');
              Route::post('{id}/rate', 'rate');
              Route::post('{id}/read', 'read');
       });
       Route::middleware(['is_admin'])->controller(BookChallengeController::class)->group(function () {
              Route::post('/{id}/add-to-challenge', 'addToChallenge');
              Route::delete('/{id}/remove-from-challenge', 'removeFromChallenge');
       });
});
Route::prefix('bookmarks')->middleware(['auth:sanctum', 'verified'])->controller(BookmarkController::class)->group(function () {
       Route::post('/', 'store');
       Route::get('/', 'index');
       Route::get('/{id}', 'show');
       Route::delete('/{id}', 'destroy');
       Route::put('/{id}', 'update');
});
Route::prefix('highlights')->middleware(['auth:sanctum', 'verified'])->controller(HighlightController::class)->group(function () {
       Route::post('/', 'store');
       Route::get('/', 'index');
       Route::get('/{id}', 'show');
       Route::delete('/{id}', 'destroy');
});
Route::prefix('challenges')->middleware(['auth:sanctum', 'verified'])->group(function () {
       Route::controller(ChallengeController::class)->group(function () {
              Route::post('/', 'store');
              Route::get('/', 'index');
              Route::get('/{id}', 'show');
              Route::delete('/{id}', 'destroy');
              Route::put('/{id}', 'update');
              Route::post('/{id}/publish', 'publish');
       });
       Route::controller(ChallengeUserController::class)->group(function () {
              Route::post('/{id}/join', 'joinChallenge');
              Route::post('/{id}/resign', 'resignChallenge');
       });
});
Route::middleware(['auth:sanctum', 'verified'])->controller(BadgeController::class)->group(function () {
       Route::delete('badges/{id}', 'destroy');
       Route::put('badges/{id}', 'update');
       Route::get('badges/{id}', 'show');
       Route::post('badges', 'store');
       Route::get('badges', 'index');
});
Route::middleware(['auth:sanctum', 'verified'])->controller(BadgeUserController::class)->group(function () {
       Route::get('my-badges', 'myBadges');
});

Route::prefix('complaints/')->middleware(['auth:sanctum', 'verified'])->controller(ComplaintController::class)->group(function () {
       Route::delete('{id}', 'destroy');
       Route::put('{id}', 'update');
       Route::get('{id}', 'show');
       Route::post('', 'store');
       Route::get('', 'index');
});
