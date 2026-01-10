<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'video_url',
        'thumbnail',
        'music_id',
        'duration',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'hashtags',
        'is_public',
        'is_featured',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'duration' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }

    public function music()
    {
        return $this->belongsTo(Song::class, 'music_id');
    }

    public function reactions()
    {
        return $this->hasMany(VideoReaction::class);
    }
}
