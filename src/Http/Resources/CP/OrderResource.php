<?php

declare(strict_types=1);

namespace Cartino\Http\Resources\CP;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'tags' => $this->tags,
            // Financial fields
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'shipping_total' => $this->shipping_total,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            'formatted_subtotal' => $this->formatted_subtotal,
            'formatted_tax_total' => $this->formatted_tax_total,
            'formatted_shipping_total' => $this->formatted_shipping_total,
            'formatted_discount_total' => $this->formatted_discount_total,
            'formatted_total' => $this->formatted_total,
            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'shipped_at' => $this->shipped_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            // Customer
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'first_name' => $this->customer->first_name,
                    'last_name' => $this->customer->last_name,
                    'full_name' => $this->customer->full_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                    'avatar_url' => $this->customer->avatar_url,
                ];
            }),
            // Order items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'formatted_price' => $item->formatted_price,
                        'total' => $item->total,
                        'formatted_total' => $item->formatted_total,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'product' => $this->when($item->relationLoaded('product'), function () use ($item) {
                            return [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'slug' => $item->product->slug,
                                'image_url' => $item->product->image_url,
                                'url' => $item->product->url,
                            ];
                        }),
                    ];
                });
            }),
            // Addresses
            'shipping_address' => $this->whenLoaded('shippingAddress', function () {
                return [
                    'id' => $this->shippingAddress->id,
                    'first_name' => $this->shippingAddress->first_name,
                    'last_name' => $this->shippingAddress->last_name,
                    'full_name' => $this->shippingAddress->full_name,
                    'company' => $this->shippingAddress->company,
                    'address_line_1' => $this->shippingAddress->address_line_1,
                    'address_line_2' => $this->shippingAddress->address_line_2,
                    'city' => $this->shippingAddress->city,
                    'state' => $this->shippingAddress->state,
                    'postal_code' => $this->shippingAddress->postal_code,
                    'country_id' => $this->shippingAddress->country_id,
                    'phone' => $this->shippingAddress->phone,
                    'formatted_address' => $this->shippingAddress->formatted_address,
                ];
            }),
            'billing_address' => $this->whenLoaded('billingAddress', function () {
                return [
                    'id' => $this->billingAddress->id,
                    'first_name' => $this->billingAddress->first_name,
                    'last_name' => $this->billingAddress->last_name,
                    'full_name' => $this->billingAddress->full_name,
                    'company' => $this->billingAddress->company,
                    'address_line_1' => $this->billingAddress->address_line_1,
                    'address_line_2' => $this->billingAddress->address_line_2,
                    'city' => $this->billingAddress->city,
                    'state' => $this->billingAddress->state,
                    'postal_code' => $this->billingAddress->postal_code,
                    'country_id' => $this->billingAddress->country_id,
                    'phone' => $this->billingAddress->phone,
                    'formatted_address' => $this->billingAddress->formatted_address,
                ];
            }),
            // Payments
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'formatted_amount' => $payment->formatted_amount,
                        'method' => $payment->method,
                        'status' => $payment->status,
                        'reference' => $payment->reference,
                        'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            // Fulfillments
            'fulfillments' => $this->whenLoaded('fulfillments', function () {
                return $this->fulfillments->map(function ($fulfillment) {
                    return [
                        'id' => $fulfillment->id,
                        'tracking_number' => $fulfillment->tracking_number,
                        'tracking_company' => $fulfillment->tracking_company,
                        'status' => $fulfillment->status,
                        'shipped_at' => $fulfillment->shipped_at?->format('Y-m-d H:i:s'),
                        'delivered_at' => $fulfillment->delivered_at?->format('Y-m-d H:i:s'),
                        'items' => $fulfillment->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'order_item_id' => $item->order_item_id,
                                'quantity' => $item->quantity,
                            ];
                        }),
                    ];
                });
            }),
            // Refunds
            'refunds' => $this->whenLoaded('refunds', function () {
                return $this->refunds->map(function ($refund) {
                    return [
                        'id' => $refund->id,
                        'amount' => $refund->amount,
                        'formatted_amount' => $refund->formatted_amount,
                        'reason' => $refund->reason,
                        'status' => $refund->status,
                        'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            // Counts
            'items_count' => $this->whenCounted('items'),
            'payments_count' => $this->whenCounted('payments'),
            'fulfillments_count' => $this->whenCounted('fulfillments'),
            'refunds_count' => $this->whenCounted('refunds'),
            // Computed values
            'customer_name' => $this->customer_name,
            'is_paid' => $this->is_paid,
            'is_fulfilled' => $this->is_fulfilled,
            'is_shipped' => $this->is_shipped,
            'is_delivered' => $this->is_delivered,
            'is_cancelled' => $this->is_cancelled,
            'is_refunded' => $this->is_refunded,
            'can_be_cancelled' => $this->can_be_cancelled,
            'can_be_fulfilled' => $this->can_be_fulfilled,
            'can_be_refunded' => $this->can_be_refunded,
            // URLs
            'admin_url' => route('cp.orders.show', $this->id),
            'edit_url' => route('cp.orders.edit', $this->id),
            'invoice_url' => route('cp.orders.invoice', $this->id),
            // Shipping info
            'shipping_method_name' => $this->shipping_method_name,
            'shipping_method_price' => $this->shipping_method_price,
            'formatted_shipping_method_price' => $this->formatted_shipping_method_price,
            'tracking_number' => $this->tracking_number,
            'tracking_company' => $this->tracking_company,
            'tracking_url' => $this->tracking_url,
            // Financial summary
            'total_paid' => $this->total_paid,
            'total_refunded' => $this->total_refunded,
            'remaining_amount' => $this->remaining_amount,
            'formatted_total_paid' => $this->formatted_total_paid,
            'formatted_total_refunded' => $this->formatted_total_refunded,
            'formatted_remaining_amount' => $this->formatted_remaining_amount,
        ];
    }
}
