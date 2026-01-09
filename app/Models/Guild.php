<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'leader_id',
        'level',
        'exp',
        'max_members',
        'member_count',
        'requirements',
        'settings',
        'total_contribution',
        'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'settings' => 'array',
        'level' => 'integer',
        'exp' => 'integer',
        'max_members' => 'integer',
        'member_count' => 'integer',
        'total_contribution' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function leader()
    {
        return $this->belongsTo(ChatUser::class, 'leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(ChatUser::class, 'guild_members', 'guild_id', 'user_id')
            ->withPivot('role', 'contribution_points')
            ->withTimestamps();
    }
}
