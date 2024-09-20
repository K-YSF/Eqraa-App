<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookChallenge extends Model
{
    use HasFactory;
    protected $fillable = ['challenge_id','book_id'];
    protected $table = 'book_challenge';
}
