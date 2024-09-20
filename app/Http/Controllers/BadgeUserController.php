<?php

namespace App\Http\Controllers;

class BadgeUserController extends Controller
{
    public function myBadges() {
        $badges = auth()->user()->badges;
        return response()->json($badges,200);
    }
}
