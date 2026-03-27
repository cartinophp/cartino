<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'channel_id' => $this->channel_id,
            'customer_group_id' => $this->customer_group_id,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'currency_code' => $this->currency_code,
        ];
    }
}
