<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'description',
        'created_by',
        'settings',
        'last_message_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(ChatUser::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(ChatUser::class, 'conversation_members', 'conversation_id', 'user_id')
            ->withPivot('role', 'is_muted', 'last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest();
    }
}
