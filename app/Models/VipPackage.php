<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'level',
        'price',
        'duration_days',
        'benefits',
        'badge_image',
        'frame_image',
        'entrance_animation',
        'color',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'level' => 'integer',
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];
}
