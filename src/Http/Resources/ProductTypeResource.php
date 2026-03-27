<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_enabled' => $this->is_enabled,
            'sort_order' => $this->sort_order,
            'meta' => $this->meta,
            'products_count' => $this->when($this->relationLoaded('products'), fn () => $this->products_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
