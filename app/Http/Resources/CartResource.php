<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user_id' => $this->user_id,
            'session_id' => $this->when(!$this->user_id, $this->session_id),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items->count(),
            'subtotal' => number_format($this->subtotal, 2),
            'tax' => number_format($this->tax, 2),
            'total' => number_format($this->total, 2),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
