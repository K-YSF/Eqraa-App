<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use HasFactory;
    protected $fillable = ['name','note','user_id','book_id','page_number'];
    public function user()  {
        return $this->belongsTo(User::class);
    }
}
