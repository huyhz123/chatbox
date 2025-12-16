<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'service_id',
        'order_id',
        'status',
        'api_provider',
        'api_order_id',
        'input_data',
        'api_request',
        'api_response',
        'result',
        'notes',
        'submitted_at',
        'processed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'api_request' => 'array',
            'api_response' => 'array',
            'submitted_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'result']);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function history()
    {
        return $this->hasMany(TicketHistory::class);
    }

    // Scopes
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helpers
    public function updateStatus($status, $message = null, $data = null)
    {
        $this->update(['status' => $status]);

        if ($status === 'processing' && !$this->processed_at) {
            $this->update(['processed_at' => now()]);
        }

        if ($status === 'completed' && !$this->completed_at) {
            $this->update(['completed_at' => now()]);
        }

        $this->history()->create([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
