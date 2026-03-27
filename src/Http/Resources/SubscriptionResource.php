<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_number' => $this->subscription_number,
            'status' => $this->status,
            'status_display' => ucfirst(str_replace('_', ' ', $this->status)),
            // Customer
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->first_name.' '.$this->customer->last_name,
                'email' => $this->customer->email,
            ]),
            // Product
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'handle' => $this->product->handle,
            ]),
            'variant' => $this->whenLoaded('variant', fn () => $this->variant
                ? [
                    'id' => $this->variant->id,
                    'title' => $this->variant->title,
                    'sku' => $this->variant->sku,
                    'price' => $this->variant->price,
                ] : null),
            // Billing
            'billing_interval' => $this->billing_interval,
            'billing_interval_count' => $this->billing_interval_count,
            'billing_frequency' => $this->billing_frequency,
            'price' => $this->price,
            'currency_id' => $this->currency_id,
            'currency' => $this->whenLoaded('currency', fn () => [
                'code' => $this->currency->code,
                'symbol' => $this->currency->symbol,
            ]),
            'billing_cycle_count' => $this->billing_cycle_count,
            'total_billed' => $this->total_billed,
            // Dates
            'started_at' => $this->started_at?->toIso8601String(),
            'trial_end_at' => $this->trial_end_at?->toIso8601String(),
            'current_period_start' => $this->current_period_start?->toIso8601String(),
            'current_period_end' => $this->current_period_end?->toIso8601String(),
            'next_billing_date' => $this->next_billing_date?->toIso8601String(),
            'paused_at' => $this->paused_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            // Payment
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            // Cancellation
            'cancel_at_period_end' => $this->cancel_at_period_end,
            'cancel_reason' => $this->cancel_reason,
            'cancel_comment' => $this->cancel_comment,
            // Pause
            'pause_reason' => $this->pause_reason,
            'pause_resumes_at' => $this->pause_resumes_at?->toIso8601String(),
            // Computed
            'is_active' => $this->is_active,
            'is_trial' => $this->is_trial,
            'is_paused' => $this->is_paused,
            'is_cancelled' => $this->is_cancelled,
            // Metadata
            'metadata' => $this->metadata,
            'notes' => $this->notes,
            'data' => $this->data,
            // Orders
            'orders_count' => $this->whenLoaded('orders', fn () => $this->orders->count()),
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
