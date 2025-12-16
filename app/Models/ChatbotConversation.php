<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'is_active',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function addMessage($role, $message, $metadata = null)
    {
        $this->messages()->create([
            'role' => $role,
            'message' => $message,
            'metadata' => $metadata,
        ]);

        $this->update(['last_message_at' => now()]);
    }
}
