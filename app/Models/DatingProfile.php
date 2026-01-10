<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photos',
        'bio',
        'interests',
        'looking_for',
        'height',
        'work',
        'education',
        'age_min',
        'age_max',
        'distance_radius',
        'preferred_gender',
        'is_active',
    ];

    protected $casts = [
        'photos' => 'array',
        'interests' => 'array',
        'height' => 'integer',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'distance_radius' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'user_id');
    }
}
