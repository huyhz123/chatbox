<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Service extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'details',
        'image',
        'images',
        'price',
        'cost',
        'special_price',
        'api_provider',
        'api_service_id',
        'min_quantity',
        'max_quantity',
        'processing_time',
        'required_fields',
        'order',
        'is_active',
        'is_featured',
        'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'required_fields' => 'array',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'special_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sold_count' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'price', 'is_active']);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Helpers
    public function getCurrentPrice()
    {
        return $this->special_price ?? $this->price;
    }

    public function getProfit()
    {
        return $this->getCurrentPrice() - $this->cost;
    }

    public function incrementSoldCount($quantity = 1)
    {
        $this->increment('sold_count', $quantity);
    }
}
