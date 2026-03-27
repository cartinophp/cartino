<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\CP;

use Cartino\Http\Resources\ProductOptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'type' => $this->type,
            'status' => $this->status,
            'featured' => $this->featured,
            // Pricing
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'cost_price' => $this->cost_price,
            'formatted_price' => $this->formatted_price,
            'formatted_compare_price' => $this->formatted_compare_price,
            // Inventory
            'track_inventory' => $this->track_inventory,
            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'allow_backorder' => $this->allow_backorder,
            'stock_status' => $this->stock_status,
            'in_stock' => $this->in_stock,
            // Physical properties
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'requires_shipping' => $this->requires_shipping,
            // SEO
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            // Additional fields
            'tags' => $this->tags,
            'vendor' => $this->vendor,
            'product_type' => $this->product_type,
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            // Relationships
            'options' => $this->whenLoaded('options', function () {
                return ProductOptionResource::collection($this->options);
            }),
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'slug' => $this->brand->slug,
                ];
            }),
            'collections' => $this->whenLoaded('collections', function () {
                return $this->collections->map(function ($collection) {
                    return [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'slug' => $collection->slug,
                    ];
                });
            }),
            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'formatted_price' => $variant->formatted_price,
                        'stock_quantity' => $variant->stock_quantity,
                        'in_stock' => $variant->in_stock,
                        'attributes' => $variant->attributes,
                        'image' => $variant->image_url,
                    ];
                });
            }),
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'file_name' => $media->file_name,
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'url' => $media->getUrl(),
                        'thumb_url' => $media->getUrl('thumb'),
                        'alt' => $media->getCustomProperty('alt'),
                        'position' => $media->getCustomProperty('position', 0),
                    ];
                });
            }),
            'orders' => $this->whenLoaded('orders', function () {
                return $this->orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'number' => $order->number,
                        'status' => $order->status,
                        'total' => $order->total,
                        'formatted_total' => $order->formatted_total,
                        'customer_name' => $order->customer_name,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            // Counts
            'options_count' => $this->whenCounted('options'),
            'variants_count' => $this->whenCounted('variants'),
            'orders_count' => $this->whenCounted('orders'),
            'collections_count' => $this->whenCounted('collections'),
            // Computed values
            'image_url' => $this->image_url,
            'thumb_url' => $this->thumb_url,
            'url' => $this->url,
            'admin_url' => route('cp.products.show', $this->id),
            'edit_url' => route('cp.products.edit', $this->id),
            'has_variants' => $this->type === 'variable',
            'is_published' => $this->status === 'published',
            'is_featured' => $this->featured,
            // Stats (when available)
            'total_sales' => $this->when(isset($this->total_sales), $this->total_sales),
            'revenue' => $this->when(isset($this->revenue), $this->revenue),
            'conversion_rate' => $this->when(isset($this->conversion_rate), $this->conversion_rate),
            'views' => $this->when(isset($this->views), $this->views),
        ];
    }
}
