<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Cartino\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Catalog
 */
class CatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'currency' => $this->currency,
            'adjustment_type' => $this->adjustment_type,
            'adjustment_direction' => $this->adjustment_direction,
            'adjustment_value' => $this->adjustment_value,
            'auto_include_new_products' => $this->auto_include_new_products,
            'is_default' => $this->is_default,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'is_published' => $this->is_published,
            'data' => $this->data,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            // Relationships (when loaded)
            'sites' => SiteResource::collection($this->whenLoaded('sites')),
            'products_count' => $this->when($this->relationLoaded('products'), fn () => $this->products->count()),
            'variants_count' => $this->when($this->relationLoaded('variants'), fn () => $this->variants->count()),
        ];
    }
}
