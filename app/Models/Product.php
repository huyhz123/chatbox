<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'details',
        'image',
        'images',
        'price',
        'cost',
        'special_price',
        'stock',
        'low_stock_alert',
        'stock_status',
        'order',
        'is_active',
        'is_featured',
        'sold_count',
        'weight',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'attributes' => 'array',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'special_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'price', 'stock', 'is_active']);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0)->where('stock_status', 'in_stock');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_alert');
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

    public function isInStock()
    {
        return $this->stock > 0 && $this->stock_status === 'in_stock';
    }

    public function isLowStock()
    {
        return $this->stock <= $this->low_stock_alert;
    }

    public function decreaseStock($quantity)
    {
        $this->decrement('stock', $quantity);
        if ($this->stock <= 0) {
            $this->update(['stock_status' => 'out_of_stock']);
        }
    }

    public function increaseStock($quantity)
    {
        $this->increment('stock', $quantity);
        if ($this->stock > 0 && $this->stock_status === 'out_of_stock') {
            $this->update(['stock_status' => 'in_stock']);
        }
    }

    public function incrementSoldCount($quantity = 1)
    {
        $this->increment('sold_count', $quantity);
    }
}
