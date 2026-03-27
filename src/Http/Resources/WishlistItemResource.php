<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wishlist_id' => $this->wishlist_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'added_at' => $this->created_at->toISOString(),
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
        ];
    }
}
