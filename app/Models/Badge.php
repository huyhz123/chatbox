<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'icon',
        'category',
        'requirements',
        'reward_coins',
        'reward_exp',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requirements' => 'array',
        'reward_coins' => 'integer',
        'reward_exp' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(ChatUser::class, 'user_badges', 'badge_id', 'user_id')
            ->withPivot('is_equipped')
            ->withTimestamps();
    }
}
