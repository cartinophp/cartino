<?php

namespace Cartino\DTO;

class ProductDto extends BaseDto
{
    public ?int $id = null;

    public ?int $site_id = null;

    public ?string $name = null;

    public ?string $handle = null;

    public ?string $description = null;

    public ?int $price = null;

    public ?int $compare_price = null;

    public ?string $status = null;

    public ?string $visibility = null;

    public ?int $brand_id = null;

    public ?string $sku = null;

    public ?int $inventory_quantity = null;

    public ?bool $track_inventory = null;

    public ?bool $continue_selling_when_out_of_stock = null;

    public ?array $images = null;

    public ?array $variants = null;

    public ?array $seo = null;

    public ?array $custom_fields = null;

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
        return ['track_inventory', 'continue_selling_when_out_of_stock'];
    }

    /**
     * Get variants class for nested DTOs.
     */
    protected function getVariantsClass(): string
    {
        return ProductVariantDto::class;
    }

    /**
     * Validate product data.
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = 'Product name is required';
        }

        if (empty($this->handle)) {
            $errors['handle'] = 'Product handle is required';
        }

        if ($this->price !== null && $this->price < 0) {
            $errors['price'] = 'Price must be greater than or equal to 0';
        }

        if ($this->compare_price !== null && $this->price !== null && $this->compare_price <= $this->price) {
            $errors['compare_price'] = 'Compare price must be greater than price';
        }

        return $errors;
    }

    /**
     * Get SEO data.
     */
    public function getSeoTitle(): ?string
    {
        return $this->seo['title'] ?? $this->name;
    }

    /**
     * Get SEO description.
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo['description'] ?? null;
    }

    /**
     * Check if product is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if product is published.
     */
    public function isPublished(): bool
    {
        return in_array($this->visibility, ['public', 'hidden']);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPrice(string $currency = 'USD'): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return number_format($this->price / 100, 2).' '.$currency;
    }
}
