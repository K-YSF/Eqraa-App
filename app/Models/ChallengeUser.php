<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeUser extends Model
{
    use HasFactory;
    protected $table = 'challenge_user';
    protected $fillable =['user_id','challenge_id','resigned','progress'];
}
