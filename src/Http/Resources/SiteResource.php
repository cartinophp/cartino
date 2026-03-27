<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'domain' => $this->domain,
            'domains' => $this->domains,
            'locale' => $this->locale,
            'lang' => $this->lang,
            'countries' => $this->countries,
            'default_currency' => $this->default_currency,
            'supported_currencies' => $this->supported_currencies,
            'supported_locales' => $this->supported_locales,
            'tax_included_in_prices' => $this->tax_included_in_prices,
            'tax_region' => $this->tax_region,
            'priority' => $this->priority,
            'is_default' => $this->is_default,
            'is_published' => $this->is_published,
            'status' => $this->status,
            'order' => $this->order,
            'published_at' => $this->published_at?->toISOString(),
            'unpublished_at' => $this->unpublished_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'attributes' => $this->attributes,
            // Relationships (when loaded)
            'channels' => ChannelResource::collection($this->whenLoaded('channels')),
            'catalogs' => CatalogResource::collection($this->whenLoaded('catalogs')),
            'channels_count' => $this->whenCounted('channels'),
            'catalogs_count' => $this->whenCounted('catalogs'),
        ];
    }
}
