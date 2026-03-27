<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'po_number' => $this->po_number,
            'status' => $this->status,
            'order_date' => $this->order_date?->toDateString(),
            'expected_delivery_date' => $this->expected_delivery_date?->toDateString(),
            'received_date' => $this->received_date?->toDateString(),
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'supplier' => $this->whenLoaded('supplier', fn () => new SupplierResource($this->supplier)),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
