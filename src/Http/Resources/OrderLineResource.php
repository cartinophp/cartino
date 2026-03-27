<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'variant' => $this->whenLoaded('variant', fn () => new ProductVariantResource($this->variant)),
        ];
    }
}
