<?php

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;

class ProductVariantResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'title' => $this->title ?: $this->getVariantTitle(),
            'sku' => $this->sku,
            'price' => $this->formatMoney($this->price),
            'compare_price' => $this->formatMoney($this->compare_price),
            'inventory' => [
                'quantity' => $this->inventory_quantity,
                'policy' => $this->inventory_policy,
                'in_stock' => $this->isInStock(),
            ],
            'shipping' => [
                'requires_shipping' => $this->requires_shipping,
                'weight' => $this->weight,
                'weight_unit' => $this->weight_unit,
                'formatted_weight' => $this->getFormattedWeight(),
            ],
            'tax' => [
                'taxable' => $this->taxable,
            ],
            'barcode' => $this->barcode,
            'option_values' => $this->option_values,
            'image' => $this->when($this->image, [
                'url' => $this->image,
                'alt' => 'Product variant image',
            ]),
            'created_at' => $this->formatTimestamp($this->created_at),
            'updated_at' => $this->formatTimestamp($this->updated_at),
        ];
    }

    /**
     * Get variant title from option values.
     */
    protected function getVariantTitle(): string
    {
        if (! empty($this->option_values)) {
            return implode(' / ', array_values($this->option_values));
        }

        return 'Default Variant';
    }

    /**
     * Check if variant is in stock.
     */
    protected function isInStock(): bool
    {
        return $this->inventory_quantity > 0 || $this->inventory_policy === 'continue';
    }

    /**
     * Get formatted weight.
     */
    protected function getFormattedWeight(): ?string
    {
        if ($this->weight === null) {
            return null;
        }

        return $this->weight.' '.($this->weight_unit ?? 'kg');
    }
}
