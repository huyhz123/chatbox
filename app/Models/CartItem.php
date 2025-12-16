<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'cartable_type',
        'cartable_id',
        'quantity',
        'price',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
    ];

    protected $appends = ['subtotal'];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the cartable model (Product, Service, File, Course)
     */
    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get item subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get item name (from cartable)
     */
    public function getNameAttribute()
    {
        return $this->cartable ? $this->cartable->name : 'Unknown Item';
    }
}
