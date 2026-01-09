<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'privacy',
        'location',
        'feeling',
        'media',
        'tagged_users',
        'likes_count',
        'comments_count',
        'shares_count',
        'is_pinned',
    ];

    protected $casts = [
        'media' => 'array',
        'tagged_users' => 'array',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'is_pinned' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
