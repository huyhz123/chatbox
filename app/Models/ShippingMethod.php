<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'base_cost',
        'calculation_type',
        'pricing_rules',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'pricing_rules' => 'array',
    ];

    /**
     * Get orders using this shipping method
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost($weight = 0, $distance = 0, $orderAmount = 0)
    {
        switch ($this->calculation_type) {
            case 'fixed':
                return $this->base_cost;

            case 'weight_based':
                // Example: $5 base + $2 per kg
                $rules = $this->pricing_rules;
                $costPerKg = $rules['cost_per_kg'] ?? 2;
                return $this->base_cost + ($weight * $costPerKg);

            case 'distance_based':
                // Example: $5 base + $0.5 per km
                $rules = $this->pricing_rules;
                $costPerKm = $rules['cost_per_km'] ?? 0.5;
                return $this->base_cost + ($distance * $costPerKm);

            case 'order_percentage':
                // Example: 5% of order amount
                $rules = $this->pricing_rules;
                $percentage = $rules['percentage'] ?? 5;
                return ($orderAmount * $percentage) / 100;

            default:
                return $this->base_cost;
        }
    }

    /**
     * Get estimated delivery time
     */
    public function getEstimatedDeliveryAttribute()
    {
        if (!$this->estimated_days_min && !$this->estimated_days_max) {
            return 'N/A';
        }

        if ($this->estimated_days_min == $this->estimated_days_max) {
            return $this->estimated_days_min . ' days';
        }

        return $this->estimated_days_min . '-' . $this->estimated_days_max . ' days';
    }
}
