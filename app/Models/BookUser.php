<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookUser extends Model
{
    use HasFactory;
    protected $table = 'book_user';
    protected $fillable = ['book_id','user_id','favorite','percentage','rating'];
}
