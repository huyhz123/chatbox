<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    protected $table = 'ticket_history';

    protected $fillable = [
        'ticket_id',
        'status',
        'message',
        'data',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
