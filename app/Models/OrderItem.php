<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'itemable_type',
        'itemable_id',
        'item_name',
        'sku',
        'quantity',
        'price',
        'cost',
        'total',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'total' => 'decimal:2',
            'options' => 'array',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    // Helpers
    public function getProfit()
    {
        return ($this->price - $this->cost) * $this->quantity;
    }
}
