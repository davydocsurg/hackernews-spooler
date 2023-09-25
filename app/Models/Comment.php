<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'comment_id',
        'text',
        'type',
        'time',
        'author_id',
        'story_id',
        'parent_comment_id',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function parentComment()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function getCommentIdAttribute()
    {
        return $this->id;
    }

    public function getCommentTextAttribute()
    {
        return $this->text;
    }
}
