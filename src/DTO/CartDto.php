<?php

namespace Cartino\DTO;

use DateTime;

class CartDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $session_id = null,
        public ?int $customer_id = null,
        public ?int $currency_id = null,
        public float $subtotal = 0.0,
        public float $tax_total = 0.0,
        public float $shipping_total = 0.0,
        public float $discount_total = 0.0,
        public float $total = 0.0,
        public array $applied_discounts = [],
        public array $shipping_address = [],
        public array $billing_address = [],
        public string $status = 'active',
        public ?DateTime $expires_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    /**
     * Create from array
     */
    public static function from(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            session_id: $data['session_id'] ?? null,
            customer_id: isset($data['customer_id']) ? ((int) $data['customer_id']) : null,
            currency_id: isset($data['currency_id']) ? ((int) $data['currency_id']) : null,
            subtotal: (float) ($data['subtotal'] ?? 0.0),
            tax_total: (float) ($data['tax_total'] ?? 0.0),
            shipping_total: (float) ($data['shipping_total'] ?? 0.0),
            discount_total: (float) ($data['discount_total'] ?? 0.0),
            total: (float) ($data['total'] ?? 0.0),
            applied_discounts: $data['applied_discounts'] ?? [],
            shipping_address: $data['shipping_address'] ?? [],
            billing_address: $data['billing_address'] ?? [],
            status: $data['status'] ?? 'active',
            expires_at: isset($data['expires_at']) ? new DateTime($data['expires_at']) : null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'id' => $this->id,
                'session_id' => $this->session_id,
                'customer_id' => $this->customer_id,
                'currency_id' => $this->currency_id,
                'subtotal' => round($this->subtotal, 2),
                'tax_total' => round($this->tax_total, 2),
                'shipping_total' => round($this->shipping_total, 2),
                'discount_total' => round($this->discount_total, 2),
                'total' => round($this->total, 2),
                'applied_discounts' => $this->applied_discounts,
                'shipping_address' => $this->shipping_address,
                'billing_address' => $this->billing_address,
                'status' => $this->status,
                'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate cart data
     */
    public function validate(): array
    {
        $errors = [];

        if (! in_array($this->status, ['active', 'abandoned', 'converted', 'expired'])) {
            $errors['status'] = 'Cart status must be active, abandoned, converted, or expired';
        }

        if ($this->subtotal < 0) {
            $errors['subtotal'] = 'Subtotal cannot be negative';
        }

        if ($this->tax_total < 0) {
            $errors['tax_total'] = 'Tax total cannot be negative';
        }

        if ($this->shipping_total < 0) {
            $errors['shipping_total'] = 'Shipping total cannot be negative';
        }

        if ($this->discount_total < 0) {
            $errors['discount_total'] = 'Discount total cannot be negative';
        }

        if ($this->total < 0) {
            $errors['total'] = 'Total cannot be negative';
        }

        if ($this->expires_at && $this->expires_at <= new DateTime) {
            $errors['expires_at'] = 'Expiration date must be in the future';
        }

        // Validate addresses
        if (! empty($this->shipping_address)) {
            $addressErrors = $this->validateAddress($this->shipping_address, 'shipping_address');
            $errors = array_merge($errors, $addressErrors);
        }

        if (! empty($this->billing_address)) {
            $addressErrors = $this->validateAddress($this->billing_address, 'billing_address');
            $errors = array_merge($errors, $addressErrors);
        }

        return $errors;
    }

    /**
     * Validate address data
     */
    private function validateAddress(array $address, string $prefix): array
    {
        $errors = [];

        $required = ['first_name', 'last_name', 'address_line_1', 'city', 'country_id'];

        foreach ($required as $field) {
            if (empty($address[$field] ?? '')) {
                $errors["{$prefix}.{$field}"] = ucfirst(str_replace('_', ' ', $field)).' is required';
            }
        }

        return $errors;
    }

    /**
     * Check if cart is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if cart is abandoned
     */
    public function isAbandoned(): bool
    {
        return $this->status === 'abandoned';
    }

    /**
     * Check if cart is converted
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /**
     * Check if cart is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expires_at && $this->expires_at <= new DateTime;
    }

    /**
     * Check if cart has customer
     */
    public function hasCustomer(): bool
    {
        return $this->customer_id !== null;
    }

    /**
     * Check if cart has shipping address
     */
    public function hasShippingAddress(): bool
    {
        return ! empty($this->shipping_address);
    }

    /**
     * Check if cart has billing address
     */
    public function hasBillingAddress(): bool
    {
        return ! empty($this->billing_address);
    }

    /**
     * Check if cart has discounts applied
     */
    public function hasDiscounts(): bool
    {
        return ! empty($this->applied_discounts) && $this->discount_total > 0;
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(string $currency = 'EUR'): string
    {
        return number_format($this->total, 2, '.', ',').' '.$currency;
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotal(string $currency = 'EUR'): string
    {
        return number_format($this->subtotal, 2, '.', ',').' '.$currency;
    }

    /**
     * Get formatted tax total
     */
    public function getFormattedTaxTotal(string $currency = 'EUR'): string
    {
        return number_format($this->tax_total, 2, '.', ',').' '.$currency;
    }

    /**
     * Get formatted shipping total
     */
    public function getFormattedShippingTotal(string $currency = 'EUR'): string
    {
        return number_format($this->shipping_total, 2, '.', ',').' '.$currency;
    }

    /**
     * Get formatted discount total
     */
    public function getFormattedDiscountTotal(string $currency = 'EUR'): string
    {
        return number_format($this->discount_total, 2, '.', ',').' '.$currency;
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): void
    {
        $this->total = ($this->subtotal + $this->tax_total + $this->shipping_total) - $this->discount_total;
        $this->total = max(0, $this->total); // Ensure total is not negative
    }

    /**
     * Check if cart needs shipping
     */
    public function needsShipping(): bool
    {
        return $this->shipping_total > 0 || empty($this->shipping_address);
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if ($this->subtotal <= 0 || $this->discount_total <= 0) {
            return 0;
        }

        return round(($this->discount_total / $this->subtotal) * 100, 2);
    }

    /**
     * Get tax percentage
     */
    public function getTaxPercentage(): float
    {
        if ($this->subtotal <= 0 || $this->tax_total <= 0) {
            return 0;
        }

        return round(($this->tax_total / $this->subtotal) * 100, 2);
    }

    /**
     * Check if cart is empty (by totals)
     */
    public function isEmpty(): bool
    {
        return $this->subtotal <= 0;
    }

    /**
     * Get expiry time remaining in minutes
     */
    public function getExpiryMinutesRemaining(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        $now = new DateTime;
        $diff = $this->expires_at->getTimestamp() - $now->getTimestamp();

        return max(0, (int) ceil($diff / 60));
    }

    /**
     * Check if cart is expiring soon (within 30 minutes)
     */
    public function isExpiringSoon(): bool
    {
        $remaining = $this->getExpiryMinutesRemaining();

        return $remaining !== null && $remaining <= 30;
    }
}
