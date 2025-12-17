<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            'item_type' => class_basename($this->item_type),
            'item_id' => $this->item_id,
            'item_name' => $this->itemable?->name ?? $this->itemable?->title ?? 'Unknown',
            'item_image' => $this->itemable?->image ?? null,
            'quantity' => $this->quantity,
            'price' => number_format($this->price, 2),
            'total' => number_format($this->price * $this->quantity, 2),
        ];
    }
}
