<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FidelityCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'card_number' => $this->card_number,
            'points' => $this->points,
            'tier' => $this->tier,
            'total_spent_amount' => $this->total_spent_amount,
            'issued_at' => $this->issued_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
        ];
    }
}
