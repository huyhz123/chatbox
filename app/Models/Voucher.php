<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'user_usage_limit',
        'start_date',
        'end_date',
        'is_active',
        'applicable_to',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'applicable_to' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get voucher usages
     */
    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * Check if voucher is valid
     */
    public function isValid()
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Voucher is not active'];
        }

        if ($this->start_date && now() < $this->start_date) {
            return ['valid' => false, 'message' => 'Voucher is not yet valid'];
        }

        if ($this->end_date && now() > $this->end_date) {
            return ['valid' => false, 'message' => 'Voucher has expired'];
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Voucher usage limit reached'];
        }

        return ['valid' => true];
    }

    /**
     * Check if user can use this voucher
     */
    public function canUserUse($userId)
    {
        $userUsageCount = $this->usages()->where('user_id', $userId)->count();

        if ($userUsageCount >= $this->user_usage_limit) {
            return ['valid' => false, 'message' => 'You have already used this voucher'];
        }

        return ['valid' => true];
    }

    /**
     * Calculate discount for order amount
     */
    public function calculateDiscount($orderAmount)
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = ($orderAmount * $this->discount_value) / 100;

            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }

            return $discount;
        }

        // fixed_amount
        return min($this->discount_value, $orderAmount);
    }

    /**
     * Apply voucher to order
     */
    public function applyToOrder($order, $userId)
    {
        $discount = $this->calculateDiscount($order->total_amount);

        // Create usage record
        VoucherUsage::create([
            'voucher_id' => $this->id,
            'user_id' => $userId,
            'order_id' => $order->id,
            'discount_amount' => $discount,
        ]);

        // Increment usage count
        $this->increment('usage_count');

        return $discount;
    }
}
