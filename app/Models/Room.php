<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'avatar',
        'category',
        'country_code',
        'type',
        'password',
        'max_members',
        'max_seats',
        'host_id',
        'settings',
        'member_count',
        'online_count',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'max_members' => 'integer',
        'max_seats' => 'integer',
        'member_count' => 'integer',
        'online_count' => 'integer',
    ];

    public function host()
    {
        return $this->belongsTo(ChatUser::class, 'host_id');
    }

    public function members()
    {
        return $this->belongsToMany(ChatUser::class, 'room_members', 'room_id', 'user_id')
            ->withPivot('role', 'seat_number', 'is_muted', 'is_speaking')
            ->withTimestamps();
    }
}
