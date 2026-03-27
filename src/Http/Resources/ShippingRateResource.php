<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipping_zone_id' => $this->shipping_zone_id,
            'name' => $this->name,
            'rate_type' => $this->rate_type,
            'rate' => $this->rate,
            'min_order_amount' => $this->min_order_amount,
            'max_order_amount' => $this->max_order_amount,
        ];
    }
}
