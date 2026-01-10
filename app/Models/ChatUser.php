<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ChatUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'chat_users';

    protected $fillable = [
        'username',
        'email',
        'phone',
        'password',
        'full_name',
        'avatar',
        'cover_photo',
        'bio',
        'gender',
        'birthday',
        'country_code',
        'language',
        'level',
        'exp',
        'vip_level',
        'vip_expire_at',
        'balance',
        'total_spent',
        'status_text',
        'online_status',
        'is_online',
        'last_seen',
        'is_verified',
        'is_banned',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'vip_expire_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_online' => 'boolean',
        'is_verified' => 'boolean',
        'is_banned' => 'boolean',
        'balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'level' => 'integer',
        'exp' => 'integer',
        'vip_level' => 'integer',
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function friends()
    {
        return $this->belongsToMany(ChatUser::class, 'friendships', 'user_id', 'friend_id')
            ->wherePivot('status', 'accepted');
    }

    public function followers()
    {
        return $this->belongsToMany(ChatUser::class, 'follows', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(ChatUser::class, 'follows', 'follower_id', 'following_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_members', 'user_id', 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function stories()
    {
        return $this->hasMany(Story::class, 'user_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'user_id');
    }

    public function liveStreams()
    {
        return $this->hasMany(LiveStream::class, 'user_id');
    }

    public function giftsSent()
    {
        return $this->hasMany(GiftTransaction::class, 'sender_id');
    }

    public function giftsReceived()
    {
        return $this->hasMany(GiftTransaction::class, 'receiver_id');
    }

    public function guild()
    {
        return $this->belongsToMany(Guild::class, 'guild_members', 'user_id', 'guild_id');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id');
    }

    public function notifications()
    {
        return $this->hasMany(ChatNotification::class, 'user_id');
    }

    public function datingProfile()
    {
        return $this->hasOne(DatingProfile::class, 'user_id');
    }

    public function privacySettings()
    {
        return $this->hasOne(UserPrivacySetting::class, 'user_id');
    }

    // Helper methods
    public function isVip(): bool
    {
        return $this->vip_level > 0 && $this->vip_expire_at && $this->vip_expire_at->isFuture();
    }

    public function addExp(int $exp): void
    {
        $this->exp += $exp;
        $this->checkLevelUp();
        $this->save();
    }

    protected function checkLevelUp(): void
    {
        $expNeeded = $this->level * 100; // Simple formula, can be customized
        if ($this->exp >= $expNeeded) {
            $this->level++;
            $this->exp -= $expNeeded;
        }
    }

    public function addCoins(float $amount, string $description = ''): void
    {
        $this->balance += $amount;
        $this->save();

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'description' => $description,
        ]);
    }

    public function deductCoins(float $amount, string $description = ''): bool
    {
        if ($this->balance < $amount) {
            return false;
        }

        $this->balance -= $amount;
        $this->save();

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'purchase',
            'amount' => -$amount,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'description' => $description,
        ]);

        return true;
    }
}
