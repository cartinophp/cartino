<?php

namespace Cartino\DTO\Cart;

use Cartino\Enums\CartStatus;
use Cartino\Models\Cart;

class CartData
{
    public function __construct(
        public int $id,
        public ?string $session_id,
        public ?int $customer_id,
        public ?string $email,
        public CartStatus $status,
        public ?array $items,
        public float $subtotal,
        public float $tax_amount,
        public float $shipping_amount,
        public float $discount_amount,
        public float $total_amount,
        public string $currency,
        public ?string $last_activity_at,
        public ?string $abandoned_at,
        public int $recovery_emails_sent,
        public ?string $last_recovery_email_sent_at,
        public bool $recovered,
        public ?string $recovered_at,
        public ?int $converted_order_id,
        public ?array $shipping_address,
        public ?array $billing_address,
        public ?array $metadata,
        public string $created_at,
        public string $updated_at,
        public ?array $customer = null,
        public ?int $items_count = null,
        public ?int $age_in_hours = null,
    ) {}

    public static function fromModel(Cart $cart): self
    {
        return new self(
            id: $cart->id,
            session_id: $cart->session_id,
            customer_id: $cart->customer_id,
            email: $cart->email,
            status: $cart->status,
            items: $cart->items,
            subtotal: (float) $cart->subtotal,
            tax_amount: (float) $cart->tax_amount,
            shipping_amount: (float) $cart->shipping_amount,
            discount_amount: (float) $cart->discount_amount,
            total_amount: (float) $cart->total_amount,
            currency: $cart->currency,
            last_activity_at: $cart->last_activity_at?->toISOString(),
            abandoned_at: $cart->abandoned_at?->toISOString(),
            recovery_emails_sent: $cart->recovery_emails_sent,
            last_recovery_email_sent_at: $cart->last_recovery_email_sent_at?->toISOString(),
            recovered: $cart->recovered,
            recovered_at: $cart->recovered_at?->toISOString(),
            converted_order_id: $cart->converted_order_id,
            shipping_address: $cart->shipping_address,
            billing_address: $cart->billing_address,
            metadata: $cart->metadata,
            created_at: $cart->created_at->toISOString(),
            updated_at: $cart->updated_at->toISOString(),
            customer: $cart->relationLoaded('customer') && $cart->customer
                ? [
                    'id' => $cart->customer->id,
                    'first_name' => $cart->customer->first_name,
                    'last_name' => $cart->customer->last_name,
                    'email' => $cart->customer->email,
                ] : null,
            items_count: collect($cart->items ?? [])->sum('quantity'),
            age_in_hours: $cart->created_at->diffInHours(now()),
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            session_id: $data['session_id'] ?? null,
            customer_id: $data['customer_id'] ?? null,
            email: $data['email'] ?? null,
            status: CartStatus::from($data['status'] ?? 'active'),
            items: $data['items'] ?? [],
            subtotal: (float) ($data['subtotal'] ?? 0),
            tax_amount: (float) ($data['tax_amount'] ?? 0),
            shipping_amount: (float) ($data['shipping_amount'] ?? 0),
            discount_amount: (float) ($data['discount_amount'] ?? 0),
            total_amount: (float) ($data['total_amount'] ?? 0),
            currency: $data['currency'] ?? 'USD',
            last_activity_at: $data['last_activity_at'] ?? null,
            abandoned_at: $data['abandoned_at'] ?? null,
            recovery_emails_sent: (int) ($data['recovery_emails_sent'] ?? 0),
            last_recovery_email_sent_at: $data['last_recovery_email_sent_at'] ?? null,
            recovered: (bool) ($data['recovered'] ?? false),
            recovered_at: $data['recovered_at'] ?? null,
            converted_order_id: $data['converted_order_id'] ?? null,
            shipping_address: $data['shipping_address'] ?? null,
            billing_address: $data['billing_address'] ?? null,
            metadata: $data['metadata'] ?? null,
            created_at: $data['created_at'] ?? now()->toISOString(),
            updated_at: $data['updated_at'] ?? now()->toISOString(),
            customer: $data['customer'] ?? null,
            items_count: $data['items_count'] ?? null,
            age_in_hours: $data['age_in_hours'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'customer_id' => $this->customer_id,
            'email' => $this->email,
            'status' => $this->status->value,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'shipping_amount' => $this->shipping_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'last_activity_at' => $this->last_activity_at,
            'abandoned_at' => $this->abandoned_at,
            'recovery_emails_sent' => $this->recovery_emails_sent,
            'last_recovery_email_sent_at' => $this->last_recovery_email_sent_at,
            'recovered' => $this->recovered,
            'recovered_at' => $this->recovered_at,
            'converted_order_id' => $this->converted_order_id,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => $this->customer,
            'items_count' => $this->items_count,
            'age_in_hours' => $this->age_in_hours,
        ];
    }
}
