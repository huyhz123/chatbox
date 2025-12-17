<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number',
        'user_id',
        'type',
        'status',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'subtotal',
        'discount',
        'discount_amount',
        'tax',
        'total',
        'total_amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'notes',
        'ip_address',
        'paid_at',
        'shipping_method_id',
        'shipping_cost',
        'tracking_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'payment_status', 'total']);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    // Helpers
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function markAsPaid($transactionId = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);
    }

    public function calculateTotal()
    {
        $subtotal = $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });

        $this->update([
            'subtotal' => $subtotal,
            'total' => $subtotal - $this->discount + $this->tax,
        ]);
    }

    public function getTotalProfit()
    {
        return $this->items->sum(function($item) {
            return ($item->price - $item->cost) * $item->quantity;
        });
    }
}
