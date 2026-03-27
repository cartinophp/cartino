<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;

class ProductOptionValueResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'color_hex' => $this->when($this->color_hex, $this->color_hex),
            'image_url' => $this->when($this->image_url, $this->image_url),
            'position' => $this->position,
            'is_default' => $this->is_default,
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }

    /**
     * Get additional meta for option value.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'has_color' => ! empty($this->color_hex),
            'has_image' => ! empty($this->image_url),
        ]);
    }
}
