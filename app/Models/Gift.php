<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'image',
        'animation_url',
        'animation_type',
        'price',
        'category',
        'duration_ms',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_ms' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(GiftTransaction::class, 'gift_id');
    }
}
