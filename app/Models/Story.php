<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'media_url',
        'text_content',
        'background',
        'music',
        'duration',
        'stickers',
        'views_count',
        'expires_at',
    ];

    protected $casts = [
        'stickers' => 'array',
        'duration' => 'integer',
        'views_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
