<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'session_id' => $this->session_id,
            'email' => $this->email,
            'status' => $this->status,
            'currency_code' => $this->currency_code,
            'total_amount' => $this->total_amount,
            'abandoned_at' => $this->abandoned_at?->toISOString(),
            'recovered_at' => $this->recovered_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->customer)),
            'items' => CartLineResource::collection($this->whenLoaded('items')),
        ];
    }
}
