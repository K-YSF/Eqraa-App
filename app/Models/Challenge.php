<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;
    protected $fillable = ['name','end_date','published','publishing_date','badges_distributed'];
    protected $casts = [
        'end_date'=>'datetime',
        'publishing_date'=>'datetime'
    ];
    public function users() {
        return $this->belongsToMany(User::class)->withPivot(['progress']);
    }
    public function badges() {
        return $this->hasMany(Badge::class);
    }
    public function books() {
        return $this->belongsToMany(Book::class);
    }
}
