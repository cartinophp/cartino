<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            // Status
            'is_active' => $this->is_active,
            'status' => $this->is_active ? 'active' : 'inactive',
            // Validity period
            'validity' => [
                'starts_at' => $this->starts_at?->toIso8601String(),
                'ends_at' => $this->ends_at?->toIso8601String(),
                'is_current' => $this->isCurrentlyValid(),
            ],
            // Relationships
            'customer_groups' => CustomerGroupResource::collection($this->whenLoaded('customerGroups')),
            'prices_count' => $this->when($this->relationLoaded('prices'), function () {
                return $this->prices->count();
            }),
            // Metadata
            'metadata' => $this->metadata,
            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }

    /**
     * Check if price list is currently valid.
     */
    protected function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
