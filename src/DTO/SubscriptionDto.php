<?php

declare(strict_types=1);

namespace Cartino\DTO;

class SubscriptionDto
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $site_id = null,
        public readonly ?int $customer_id = null,
        public readonly ?int $product_id = null,
        public readonly ?int $product_variant_id = null,
        public readonly ?string $subscription_number = null,
        public readonly ?string $status = null,
        public readonly ?string $billing_interval = null,
        public readonly ?int $billing_interval_count = null,
        public readonly ?float $price = null,
        public readonly ?int $currency_id = null,
        public readonly ?string $started_at = null,
        public readonly ?string $trial_end_at = null,
        public readonly ?string $current_period_start = null,
        public readonly ?string $current_period_end = null,
        public readonly ?string $next_billing_date = null,
        public readonly ?string $paused_at = null,
        public readonly ?string $cancelled_at = null,
        public readonly ?string $ended_at = null,
        public readonly ?string $payment_method = null,
        public readonly ?array $payment_details = null,
        public readonly ?int $billing_cycle_count = null,
        public readonly ?float $total_billed = null,
        public readonly ?string $cancel_reason = null,
        public readonly ?string $cancel_comment = null,
        public readonly ?bool $cancel_at_period_end = null,
        public readonly ?string $pause_reason = null,
        public readonly ?string $pause_resumes_at = null,
        public readonly ?array $metadata = null,
        public readonly ?string $notes = null,
        public readonly ?array $data = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            site_id: $data['site_id'] ?? null,
            customer_id: $data['customer_id'] ?? null,
            product_id: $data['product_id'] ?? null,
            product_variant_id: $data['product_variant_id'] ?? null,
            subscription_number: $data['subscription_number'] ?? null,
            status: $data['status'] ?? null,
            billing_interval: $data['billing_interval'] ?? null,
            billing_interval_count: $data['billing_interval_count'] ?? null,
            price: $data['price'] ?? null,
            currency_id: $data['currency_id'] ?? null,
            started_at: $data['started_at'] ?? null,
            trial_end_at: $data['trial_end_at'] ?? null,
            current_period_start: $data['current_period_start'] ?? null,
            current_period_end: $data['current_period_end'] ?? null,
            next_billing_date: $data['next_billing_date'] ?? null,
            paused_at: $data['paused_at'] ?? null,
            cancelled_at: $data['cancelled_at'] ?? null,
            ended_at: $data['ended_at'] ?? null,
            payment_method: $data['payment_method'] ?? null,
            payment_details: $data['payment_details'] ?? null,
            billing_cycle_count: $data['billing_cycle_count'] ?? null,
            total_billed: $data['total_billed'] ?? null,
            cancel_reason: $data['cancel_reason'] ?? null,
            cancel_comment: $data['cancel_comment'] ?? null,
            cancel_at_period_end: $data['cancel_at_period_end'] ?? null,
            pause_reason: $data['pause_reason'] ?? null,
            pause_resumes_at: $data['pause_resumes_at'] ?? null,
            metadata: $data['metadata'] ?? null,
            notes: $data['notes'] ?? null,
            data: $data['data'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'id' => $this->id,
                'site_id' => $this->site_id,
                'customer_id' => $this->customer_id,
                'product_id' => $this->product_id,
                'product_variant_id' => $this->product_variant_id,
                'subscription_number' => $this->subscription_number,
                'status' => $this->status,
                'billing_interval' => $this->billing_interval,
                'billing_interval_count' => $this->billing_interval_count,
                'price' => $this->price,
                'currency_id' => $this->currency_id,
                'started_at' => $this->started_at,
                'trial_end_at' => $this->trial_end_at,
                'current_period_start' => $this->current_period_start,
                'current_period_end' => $this->current_period_end,
                'next_billing_date' => $this->next_billing_date,
                'paused_at' => $this->paused_at,
                'cancelled_at' => $this->cancelled_at,
                'ended_at' => $this->ended_at,
                'payment_method' => $this->payment_method,
                'payment_details' => $this->payment_details,
                'billing_cycle_count' => $this->billing_cycle_count,
                'total_billed' => $this->total_billed,
                'cancel_reason' => $this->cancel_reason,
                'cancel_comment' => $this->cancel_comment,
                'cancel_at_period_end' => $this->cancel_at_period_end,
                'pause_reason' => $this->pause_reason,
                'pause_resumes_at' => $this->pause_resumes_at,
                'metadata' => $this->metadata,
                'notes' => $this->notes,
                'data' => $this->data,
            ],
            fn ($value) => $value !== null,
        );
    }

    public function toCreateArray(): array
    {
        $data = $this->toArray();
        unset($data['id']);

        return $data;
    }
}
