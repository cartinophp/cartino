<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;

class ProductResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'description' => $this->description,
            'price' => $this->formatMoney($this->price),
            'compare_price' => $this->formatMoney($this->compare_price),
            'status' => $this->status,
            'visibility' => $this->visibility,
            'sku' => $this->sku,
            'inventory' => [
                'quantity' => $this->inventory_quantity,
                'track' => $this->track_inventory,
                'continue_selling' => $this->continue_selling_when_out_of_stock,
            ],
            'seo' => $this->when($this->seo, $this->seo),
            'images' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'thumb' => $media->getUrl('thumb'),
                        'alt' => $media->getCustomProperty('alt'),
                    ];
                });
            }),
            'options' => $this->whenIncluded('options', function () {
                return ProductOptionResource::collection($this->whenLoaded('options'));
            }),
            'variants' => $this->whenIncluded('variants', function () {
                return ProductVariantResource::collection($this->whenLoaded('variants'));
            }),
            'category' => $this->whenIncluded('category', function () {
                return new CategoryResource($this->whenLoaded('category'));
            }),
            'brand' => $this->whenIncluded('brand', function () {
                return new BrandResource($this->whenLoaded('brand'));
            }),
            'collections' => $this->whenIncluded('collections', function () {
                return CollectionResource::collection($this->whenLoaded('collections'));
            }),
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }

    /**
     * Get additional meta for product.
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'options_count' => $this->whenLoaded('options', fn () => $this->options->count()),
            'variants_count' => $this->whenLoaded('variants', fn () => $this->variants->count()),
            'images_count' => $this->whenLoaded('media', fn () => $this->media->count()),
            'is_active' => $this->status === 'active',
            'is_published' => in_array($this->visibility, ['public', 'hidden']),
        ]);
    }
}
