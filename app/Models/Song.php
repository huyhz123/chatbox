<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'language',
        'genre',
        'audio_url',
        'lyrics_url',
        'instrumental_url',
        'duration',
        'thumbnail',
        'plays_count',
        'favorites_count',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'plays_count' => 'integer',
        'favorites_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function karaokeSessions()
    {
        return $this->hasMany(KaraokeSession::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'music_id');
    }
}
