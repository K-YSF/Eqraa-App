<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;
    protected $fillable = ['name','details','challenge_id'];
    public function users() {
        return $this->belongsToMany(User::class);
    }
    public function challenge() {
        return $this->belongsTo(Challenge::class);
    }
}
