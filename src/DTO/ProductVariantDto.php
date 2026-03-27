<?php

namespace Cartino\DTO;

class ProductVariantDto extends BaseDto
{
    public ?int $id = null;

    public ?int $product_id = null;

    public ?string $title = null;

    public ?string $sku = null;

    public ?int $price = null;

    public ?int $compare_price = null;

    public ?int $inventory_quantity = null;

    public ?string $inventory_policy = null;

    public ?string $fulfillment_service = null;

    public ?bool $requires_shipping = null;

    public ?bool $taxable = null;

    public ?string $barcode = null;

    public ?float $weight = null;

    public ?string $weight_unit = null;

    public ?array $option_values = null;

    public ?string $image = null;

    /**
     * Get money fields.
     */
    protected function getMoneyFields(): array
    {
        return ['price', 'compare_price'];
    }

    /**
     * Get boolean fields.
     */
    protected function getBooleanFields(): array
    {
        return ['requires_shipping', 'taxable'];
    }

    /**
     * Get variant title from options.
     */
    public function getVariantTitle(): string
    {
        if (! empty($this->title)) {
            return $this->title;
        }

        if (! empty($this->option_values)) {
            return implode(' / ', array_values($this->option_values));
        }

        return 'Default Variant';
    }

    /**
     * Check if variant is in stock.
     */
    public function isInStock(): bool
    {
        return $this->inventory_quantity > 0 || $this->inventory_policy === 'continue';
    }

    /**
     * Get formatted weight.
     */
    public function getFormattedWeight(): ?string
    {
        if ($this->weight === null) {
            return null;
        }

        return $this->weight.' '.($this->weight_unit ?? 'kg');
    }
}
