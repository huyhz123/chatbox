<?php

namespace App\Events;

use App\Models\GiftTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GiftTransaction $transaction;
    public string $channelName;

    /**
     * Create a new event instance.
     */
    public function __construct(GiftTransaction $transaction, string $channelName)
    {
        $this->transaction = $transaction;
        $this->channelName = $channelName;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->channelName),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'sender' => [
                'id' => $this->transaction->sender->id,
                'username' => $this->transaction->sender->username,
                'full_name' => $this->transaction->sender->full_name,
                'avatar' => $this->transaction->sender->avatar,
                'level' => $this->transaction->sender->level,
                'vip_level' => $this->transaction->sender->vip_level,
            ],
            'receiver' => [
                'id' => $this->transaction->receiver->id,
                'username' => $this->transaction->receiver->username,
                'full_name' => $this->transaction->receiver->full_name,
            ],
            'gift' => [
                'id' => $this->transaction->gift->id,
                'name' => $this->transaction->gift->name,
                'icon' => $this->transaction->gift->icon,
                'animation_url' => $this->transaction->gift->animation_url,
                'animation_type' => $this->transaction->gift->animation_type,
                'duration_ms' => $this->transaction->gift->duration_ms,
            ],
            'quantity' => $this->transaction->quantity,
            'total_price' => $this->transaction->total_price,
            'context_type' => $this->transaction->context_type,
            'context_id' => $this->transaction->context_id,
            'created_at' => $this->transaction->created_at->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'gift.sent';
    }
}
