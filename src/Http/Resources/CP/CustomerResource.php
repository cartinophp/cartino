<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\CP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'status' => $this->status,
            'notes' => $this->notes,
            'accepts_marketing' => $this->accepts_marketing,
            'tax_exempt' => $this->tax_exempt,
            'tags' => $this->tags,
            'customer_group' => $this->whenLoaded('customerGroup', function () {
                return [
                    'id' => $this->customerGroup->id,
                    'name' => $this->customerGroup->name,
                ];
            }),
            'addresses_count' => $this->whenCounted('addresses'),
            'orders_count' => $this->whenCounted('orders'),
            'total_spent' => $this->total_spent ?? 0,
            'average_order_value' => $this->average_order_value ?? 0,
            'last_order_at' => $this->last_order_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
