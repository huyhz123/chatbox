<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivacySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_visibility',
        'who_can_message',
        'who_can_call',
        'show_online_status',
        'show_last_seen',
        'show_read_receipts',
        'allow_tags',
        'allow_mentions',
        'show_location',
        'allow_friend_requests',
    ];

    protected $casts = [
        'show_online_status' => 'boolean',
        'show_last_seen' => 'boolean',
        'show_read_receipts' => 'boolean',
        'allow_tags' => 'boolean',
        'allow_mentions' => 'boolean',
        'show_location' => 'boolean',
        'allow_friend_requests' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }
}
