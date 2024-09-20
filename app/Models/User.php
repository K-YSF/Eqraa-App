<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'social_links',
        'avatar',
        'bio',
        'number_of_friends',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'social_links' => 'array'
    ];
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class)->withPivot(['progress'])->wherePivot('resigned', '<>', 1);
    }
    public function books()
    {
        return $this->belongsToMany(Book::class)->withPivot(['id', 'percentage', 'favorite', 'rating'])->wherePivot('percentage', '<>', '0');
    }
    public function bookmarks()
    {
        return $this->hasMany(BookMark::class);
    }
    public function highlights()
    {
        return $this->hasMany(Highlight::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')->wherePivot('accepted', '=', 1);
    }
    public function scopeFilters(Builder $query, $searchQuery)
    {
        $query
            ->where('name', 'like', '%' . $searchQuery . '%')
            ->orWhere('bio', 'like', '%' . $searchQuery . '%')
            ->orWhere('social_links', 'like', '%' . $searchQuery . '%');
    }
}
