<?php

namespace Cartino\DTO;

use DateTime;

class OrderDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $order_number = '',
        public ?int $customer_id = null,
        public string $customer_email = '',
        public array $customer_details = [],
        public ?int $currency_id = null,
        public float $subtotal = 0.0,
        public float $tax_total = 0.0,
        public float $shipping_total = 0.0,
        public float $discount_total = 0.0,
        public float $total = 0.0,
        public string $status = 'pending',
        public string $payment_status = 'pending',
        public string $fulfillment_status = 'unfulfilled',
        public array $shipping_address = [],
        public array $billing_address = [],
        public array $applied_discounts = [],
        public ?string $shipping_method = null,
        public ?string $payment_method = null,
        public array $payment_details = [],
        public ?string $notes = null,
        public ?DateTime $shipped_at = null,
        public ?DateTime $delivered_at = null,
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
            order_number: $data['order_number'] ?? '',
            customer_id: isset($data['customer_id']) ? ((int) $data['customer_id']) : null,
            customer_email: $data['customer_email'] ?? '',
            customer_details: $data['customer_details'] ?? [],
            currency_id: isset($data['currency_id']) ? ((int) $data['currency_id']) : null,
            subtotal: (float) ($data['subtotal'] ?? 0.0),
            tax_total: (float) ($data['tax_total'] ?? 0.0),
            shipping_total: (float) ($data['shipping_total'] ?? 0.0),
            discount_total: (float) ($data['discount_total'] ?? 0.0),
            total: (float) ($data['total'] ?? 0.0),
            status: $data['status'] ?? 'pending',
            payment_status: $data['payment_status'] ?? 'pending',
            fulfillment_status: $data['fulfillment_status'] ?? 'unfulfilled',
            shipping_address: $data['shipping_address'] ?? [],
            billing_address: $data['billing_address'] ?? [],
            applied_discounts: $data['applied_discounts'] ?? [],
            shipping_method: $data['shipping_method'] ?? null,
            payment_method: $data['payment_method'] ?? null,
            payment_details: $data['payment_details'] ?? [],
            notes: $data['notes'] ?? null,
            shipped_at: isset($data['shipped_at']) ? new DateTime($data['shipped_at']) : null,
            delivered_at: isset($data['delivered_at']) ? new DateTime($data['delivered_at']) : null,
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
                'order_number' => $this->order_number,
                'customer_id' => $this->customer_id,
                'customer_email' => $this->customer_email,
                'customer_details' => $this->customer_details,
                'currency_id' => $this->currency_id,
                'subtotal' => round($this->subtotal, 2),
                'tax_total' => round($this->tax_total, 2),
                'shipping_total' => round($this->shipping_total, 2),
                'discount_total' => round($this->discount_total, 2),
                'total' => round($this->total, 2),
                'status' => $this->status,
                'payment_status' => $this->payment_status,
                'fulfillment_status' => $this->fulfillment_status,
                'shipping_address' => $this->shipping_address,
                'billing_address' => $this->billing_address,
                'applied_discounts' => $this->applied_discounts,
                'shipping_method' => $this->shipping_method,
                'payment_method' => $this->payment_method,
                'payment_details' => $this->payment_details,
                'notes' => $this->notes,
                'shipped_at' => $this->shipped_at?->format('Y-m-d H:i:s'),
                'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate order data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->customer_email))) {
            $errors['customer_email'] = 'Customer email is required';
        }

        if (! filter_var($this->customer_email, FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Customer email must be valid';
        }

        if (
            ! in_array($this->status, [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded',
            ])
        ) {
            $errors['status'] = 'Invalid order status';
        }

        if (! in_array($this->payment_status, ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])) {
            $errors['payment_status'] = 'Invalid payment status';
        }

        if (! in_array($this->fulfillment_status, ['unfulfilled', 'partial', 'fulfilled', 'shipped', 'delivered'])) {
            $errors['fulfillment_status'] = 'Invalid fulfillment status';
        }

        if ($this->subtotal < 0) {
            $errors['subtotal'] = 'Subtotal cannot be negative';
        }

        if ($this->total < 0) {
            $errors['total'] = 'Total cannot be negative';
        }

        if ($this->shipping_total < 0) {
            $errors['shipping_total'] = 'Shipping total cannot be negative';
        }

        if ($this->tax_total < 0) {
            $errors['tax_total'] = 'Tax total cannot be negative';
        }

        if ($this->discount_total < 0) {
            $errors['discount_total'] = 'Discount total cannot be negative';
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
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is fulfilled
     */
    public function isFulfilled(): bool
    {
        return $this->fulfillment_status === 'fulfilled';
    }

    /**
     * Check if order is shipped
     */
    public function isShipped(): bool
    {
        return $this->fulfillment_status === 'shipped';
    }

    /**
     * Check if order is delivered
     */
    public function isDelivered(): bool
    {
        return $this->fulfillment_status === 'delivered';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
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
     * Get customer display name
     */
    public function getCustomerDisplayName(): string
    {
        if (! empty($this->customer_details['name'])) {
            return $this->customer_details['name'];
        }

        if (! empty($this->customer_details['first_name']) && ! empty($this->customer_details['last_name'])) {
            return $this->customer_details['first_name'].' '.$this->customer_details['last_name'];
        }

        return $this->customer_email;
    }

    /**
     * Check if can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && $this->payment_status !== 'paid';
    }

    /**
     * Check if can be shipped
     */
    public function canBeShipped(): bool
    {
        return
            $this->status === 'confirmed' &&
            $this->payment_status === 'paid' &&
            $this->fulfillment_status === 'unfulfilled';
    }

    /**
     * Calculate totals
     */
    public function calculateTotal(): void
    {
        $this->total = ($this->subtotal + $this->tax_total + $this->shipping_total) - $this->discount_total;
    }
}
