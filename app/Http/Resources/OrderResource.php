<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items->count(),
            'subtotal' => number_format($this->subtotal ?? $this->total_amount, 2),
            'shipping_cost' => number_format($this->shipping_cost ?? 0, 2),
            'discount_amount' => number_format($this->discount_amount ?? 0, 2),
            'total_amount' => number_format($this->total_amount, 2),
            'payment_status' => $this->payment?->status ?? 'pending',
            'payment_method' => $this->payment?->payment_method ?? null,
            'shipping_method' => $this->whenLoaded('shippingMethod', [
                'id' => $this->shippingMethod?->id,
                'name' => $this->shippingMethod?->name,
                'description' => $this->shippingMethod?->description,
            ]),
            'tracking_number' => $this->tracking_number,
            'created_at' => $this->created_at?->format('M d, Y H:i'),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
