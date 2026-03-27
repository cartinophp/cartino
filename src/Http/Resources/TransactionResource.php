<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'transaction_id' => $this->transaction_id,
            'gateway' => $this->gateway,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency_code' => $this->currency_code,
            'gateway_response' => $this->gateway_response,
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'order' => $this->whenLoaded('order', fn () => new OrderResource($this->order)),
        ];
    }
}
