<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'cart_id' => $this->cart_id,
            'item_type' => class_basename($this->cartable_type),
            'item_id' => $this->cartable_id,
            'item_name' => $this->cartable?->name ?? $this->cartable?->title ?? 'Unknown',
            'item_image' => $this->cartable?->image ?? null,
            'quantity' => $this->quantity,
            'price' => number_format($this->price, 2),
            'subtotal' => number_format($this->subtotal, 2),
            'options' => $this->options,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
