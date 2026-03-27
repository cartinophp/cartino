<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;

class ProductOptionResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'position' => $this->position,
            'is_global' => $this->is_global,
            'is_required' => $this->is_required,
            'is_visible' => $this->is_visible,
            'use_for_variants' => $this->use_for_variants,
            'configuration' => $this->when($this->configuration, $this->configuration),
            'values' => ProductOptionValueResource::collection($this->whenLoaded('values')),
        ];
    }

    /**
     * Get additional meta for option.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'values_count' => $this->whenLoaded('values', fn () => $this->values->count()),
            'is_color_swatch' => $this->type === 'color',
            'is_variant_option' => $this->use_for_variants,
        ]);
    }
}
