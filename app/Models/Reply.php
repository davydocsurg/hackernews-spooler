<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reply_id',
        'text',
        'score',
        'time',
        'author_id',
        'comment_id',
        'parent_reply_id'
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
