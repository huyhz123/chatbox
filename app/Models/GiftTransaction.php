<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'gift_id',
        'quantity',
        'total_price',
        'context_type',
        'context_id',
        'is_public',
        'message',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'integer',
        'is_public' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(ChatUser::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(ChatUser::class, 'receiver_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
}
