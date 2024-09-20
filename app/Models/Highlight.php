<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Highlight extends Model
{
    use HasFactory;
    protected $fillable = ['content','user_id','book_id','page_number'];
    public function user()  {
        return $this->belongsTo(User::class);
    }
}
