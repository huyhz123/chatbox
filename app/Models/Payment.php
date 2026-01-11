<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'payable_type',  // Transaction, GiftTransaction, etc.
        'payable_id',
        'payment_gateway',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'request_data',
        'response_data',
        'error_message',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_data' => 'array',
            'response_data' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'amount']);
    }

    // Relationships
    public function payable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helpers
    public function markAsCompleted($transactionId = null, $responseData = null)
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'response_data' => $responseData,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage, $responseData = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_data' => $responseData,
        ]);
    }
}
