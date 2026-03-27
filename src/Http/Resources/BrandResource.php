<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Cartino\Models\Brand;
use Illuminate\Http\Request;

/**
 * @mixin Brand
 */
class BrandResource extends BaseResource
{
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'website' => $this->website,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'image_url' => $this->image_url,
            'products_count' => $this->whenCounted('products') ?? $this->products()->count(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Display values
            'display_status' => $this->status === 'active' ? 'Attivo' : 'Inattivo',
            'is_active' => $this->status === 'active',
        ];
    }
}
