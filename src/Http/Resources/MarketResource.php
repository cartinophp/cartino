<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            // Geographic
            'countries' => $this->countries,
            'tax_region' => $this->tax_region,
            // Currency
            'currencies' => [
                'default' => $this->default_currency,
                'supported' => $this->getCurrencies(),
            ],
            // Locale
            'locales' => [
                'default' => $this->default_locale,
                'supported' => $this->getLocales(),
            ],
            // Tax configuration
            'tax' => [
                'included_in_prices' => $this->tax_included_in_prices,
                'region' => $this->tax_region,
            ],
            // Catalog
            'catalog' => [
                'id' => $this->catalog_id,
                'use_catalog_prices' => $this->use_catalog_prices,
                'catalog' => new CatalogResource($this->whenLoaded('catalog')),
            ],
            // Configuration
            'payment_methods' => $this->payment_methods,
            'shipping_methods' => $this->shipping_methods,
            'fulfillment_locations' => $this->fulfillment_locations,
            // Status
            'status' => $this->status,
            'is_default' => $this->is_default,
            'is_published' => $this->is_published,
            'priority' => $this->priority,
            // Dates
            'published_at' => $this->published_at?->toIso8601String(),
            'unpublished_at' => $this->unpublished_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            // Relationships
            'sites' => SiteResource::collection($this->whenLoaded('sites')),
            // Meta
            'settings' => $this->settings,
            'metadata' => $this->metadata,
        ];
    }
}
