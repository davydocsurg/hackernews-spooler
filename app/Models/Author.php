<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
    ];

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function askPosts()
    {
        return $this->hasMany(AskPost::class);
    }

    public function jobPosts()
    {
        return $this->hasMany(JobPost::class);
    }

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function getAuthorIdAttribute()
    {
        return $this->id;
    }

    public function getAuthorUsernameAttribute()
    {
        return $this->username;
    }
}
