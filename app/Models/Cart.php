<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get cart subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Get cart tax (10%)
     */
    public function getTaxAttribute()
    {
        return $this->subtotal * 0.10;
    }

    /**
     * Get cart total
     */
    public function getTotalAttribute()
    {
        return $this->subtotal + $this->tax;
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Add item to cart
     */
    public function addItem($cartable, $quantity = 1, $options = [])
    {
        $existingItem = $this->items()
            ->where('cartable_type', get_class($cartable))
            ->where('cartable_id', $cartable->id)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        }

        return $this->items()->create([
            'cartable_type' => get_class($cartable),
            'cartable_id' => $cartable->id,
            'quantity' => $quantity,
            'price' => $cartable->price,
            'options' => $options,
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem($itemId)
    {
        return $this->items()->where('id', $itemId)->delete();
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity($itemId, $quantity)
    {
        $item = $this->items()->find($itemId);

        if ($item) {
            $item->update(['quantity' => $quantity]);
            return $item;
        }

        return null;
    }

    /**
     * Clear all items
     */
    public function clear()
    {
        return $this->items()->delete();
    }
}
