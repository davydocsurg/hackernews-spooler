<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AskPost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ask_post_id',
        'title',
        'type',
        'text',
        'url',
        'descendants',
        'score',
        'time',
        'author_id',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getAskPostIdAttribute()
    {
        return $this->ask_post_id;
    }
}
