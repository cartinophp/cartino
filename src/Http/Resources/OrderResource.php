<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'currency_code' => $this->currency_code,
            'subtotal_amount' => $this->subtotal_amount,
            'shipping_amount' => $this->shipping_amount,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'placed_at' => $this->placed_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'fulfilled_at' => $this->fulfilled_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            // Relations
            'customer' => $this->whenLoaded('customer', fn () => new CustomerResource($this->customer)),
            'items' => OrderLineResource::collection($this->whenLoaded('items')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
