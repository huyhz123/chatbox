<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail',
        'category',
        'stream_key',
        'rtmp_url',
        'hls_url',
        'agora_channel_id',
        'viewer_count',
        'peak_viewers',
        'total_gifts_value',
        'likes_count',
        'comments_count',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_seconds',
        'is_featured',
    ];

    protected $casts = [
        'viewer_count' => 'integer',
        'peak_viewers' => 'integer',
        'total_gifts_value' => 'decimal:2',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'duration_seconds' => 'integer',
        'is_featured' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }

    public function viewers()
    {
        return $this->hasMany(StreamViewer::class, 'stream_id');
    }

    public function gifts()
    {
        return $this->morphMany(GiftTransaction::class, 'context');
    }
}
