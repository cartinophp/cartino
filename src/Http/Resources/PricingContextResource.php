<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Cartino\DTO\PricingContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingContextResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  PricingContext  $resource
     */
    public function toArray(Request $request): array
    {
        return [
            'market' => [
                'id' => $this->resource->market?->id,
                'code' => $this->resource->market?->code,
                'name' => $this->resource->market?->name,
                'type' => $this->resource->market?->type,
            ],
            'site' => [
                'id' => $this->resource->site?->id,
                'handle' => $this->resource->site?->handle,
                'name' => $this->resource->site?->name,
                'domain' => $this->resource->site?->domain,
            ],
            'channel' => [
                'id' => $this->resource->channel?->id,
                'slug' => $this->resource->channel?->slug,
                'name' => $this->resource->channel?->name,
                'type' => $this->resource->channel?->type,
            ],
            'catalog' => [
                'id' => $this->resource->catalog?->id,
                'slug' => $this->resource->catalog?->slug,
                'title' => $this->resource->catalog?->title,
            ],
            'customer' => [
                'id' => $this->resource->customer?->id,
                'email' => $this->resource->customer?->email,
                'customer_group_id' => $this->resource->customerGroup?->id,
            ],
            'currency' => $this->resource->currency,
            'locale' => $this->resource->locale,
            'quantity' => $this->resource->quantity,
            'country_code' => $this->resource->countryCode,
            'tax' => [
                'inclusive' => $this->resource->isTaxInclusive(),
                'region' => $this->resource->getTaxRegion(),
            ],
            'metadata' => $this->resource->metadata,
        ];
    }
}
