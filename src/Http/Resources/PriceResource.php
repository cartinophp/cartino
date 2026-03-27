<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Amount (in cents)
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'amount_display' => $this->currency.' '.$this->formatted_amount,
            // Compare at price
            'compare_at_amount' => $this->compare_at_amount,
            'formatted_compare_at' => $this->formatted_compare_at,
            'discount_percentage' => $this->discount_percentage,
            // Currency
            'currency' => $this->currency,
            // Tax
            'tax' => [
                'included' => $this->tax_included,
                'rate' => $this->tax_rate,
                'amount_with_tax' => $this->getAmountWithTax(),
                'amount_without_tax' => $this->getAmountWithoutTax(),
            ],
            // Quantity tiers
            'quantity' => [
                'min' => $this->min_quantity,
                'max' => $this->max_quantity,
            ],
            // Context
            'context' => [
                'market_id' => $this->market_id,
                'site_id' => $this->site_id,
                'channel_id' => $this->channel_id,
                'price_list_id' => $this->price_list_id,
            ],
            // Validity
            'validity' => [
                'starts_at' => $this->starts_at?->toIso8601String(),
                'ends_at' => $this->ends_at?->toIso8601String(),
                'is_active' => $this->isActive(),
            ],
            // Relationships
            'variant' => new ProductVariantResource($this->whenLoaded('variant')),
            'market' => new MarketResource($this->whenLoaded('market')),
            'site' => new SiteResource($this->whenLoaded('site')),
            'channel' => new ChannelResource($this->whenLoaded('channel')),
            'price_list' => new PriceListResource($this->whenLoaded('priceList')),
            // Meta
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
