<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Cartino\Models\Product;
use Cartino\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierResource extends BaseResource
{
    /**
     * Transform the resource data.
     */
    protected function transformData(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'contact_person' => $this->contact_person,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_code' => $this->country_code,
            'website' => $this->website,
            'tax_number' => $this->tax_number,
            'status' => $this->status,
            'priority' => $this->priority,
            'rating' => $this->rating,
            'is_preferred' => $this->is_preferred,
            'is_verified' => $this->is_verified,
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
            'shipping_terms' => $this->shipping_terms,
            'lead_time_days' => $this->lead_time_days,
            'minimum_order_value' => $this->minimum_order_value,
            'currency_code' => $this->currency_code,
            'on_time_delivery_rate' => $this->on_time_delivery_rate,
            'average_response_time_hours' => $this->average_response_time_hours,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Relationships
            'site' => $this->whenIncluded('site', fn () => [
                'id' => $this->site?->id,
                'name' => $this->site?->name,
            ]),
            'products_count' => $this->whenIncluded(
                'products',
                fn () => $this->products_count ?? $this->products()->count(),
            ),
            'products' => $this->whenIncluded('products', fn () => $this->products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price_amount,
                'status' => $product->status,
            ])),
            'purchase_orders_count' => $this->whenIncluded(
                'purchase_orders',
                fn () => $this->purchase_orders_count ?? $this->purchaseOrders()->count(),
            ),
            'purchase_orders' => $this->whenIncluded(
                'purchase_orders',
                fn () => $this->purchaseOrders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'order_date' => $order->order_date?->toISOString(),
                    'expected_delivery_date' => $order->expected_delivery_date?->toISOString(),
                    'delivery_date' => $order->delivery_date?->toISOString(),
                    'delivered_on_time' => $order->delivered_on_time,
                ]),
            ),
            // Performance metrics
            'performance_metrics' => $this->whenIncluded('performance', fn () => [
                'total_orders' => $this->purchaseOrders()->count(),
                'on_time_deliveries' => $this->purchaseOrders()->where('delivered_on_time', true)->count(),
                'on_time_delivery_rate' => $this->on_time_delivery_rate,
                'average_delivery_time' => $this->calculateAverageDeliveryTime(),
                'total_value' => $this->purchaseOrders()->sum('total_amount'),
                'average_order_value' => $this->calculateAverageOrderValue(),
                'rating' => $this->rating,
            ]),
            // Display values
            'display_status' => $this->getDisplayStatus(),
            'display_priority' => $this->getDisplayPriority(),
            'formatted_rating' => number_format($this->rating, 1).'/5',
            'is_active' => $this->status === 'active',
        ];
    }

    /**
     * Get additional meta information
     */
    protected function getMeta(Request $request): array
    {
        return array_merge(parent::getMeta($request), [
            'supplier' => [
                'can_edit' => $request->user()?->can('update', $this->resource),
                'can_delete' => $request->user()?->can('delete', $this->resource),
                'can_view_purchase_orders' => $request->user()?->can('viewAny', PurchaseOrder::class),
                'can_manage_products' => $request->user()?->can('viewAny', Product::class),
            ],
        ]);
    }

    /**
     * Calculate average delivery time in days
     */
    private function calculateAverageDeliveryTime(): ?float
    {
        $deliveredOrders = $this->purchaseOrders()
            ->whereNotNull('delivery_date')
            ->whereNotNull('order_date')
            ->get();

        if ($deliveredOrders->isEmpty()) {
            return null;
        }

        $totalDays = $deliveredOrders->sum(function ($order) {
            return $order->order_date->diffInDays($order->delivery_date);
        });

        return round($totalDays / $deliveredOrders->count(), 1);
    }

    /**
     * Calculate average order value
     */
    private function calculateAverageOrderValue(): ?float
    {
        $orders = $this->purchaseOrders();
        $count = $orders->count();

        if ($count === 0) {
            return null;
        }

        return round($orders->sum('total_amount') / $count, 2);
    }

    /**
     * Get display status
     */
    private function getDisplayStatus(): string
    {
        return match ($this->status) {
            'active' => 'Attivo',
            'inactive' => 'Inattivo',
            'suspended' => 'Sospeso',
            default => 'Sconosciuto',
        };
    }

    /**
     * Get display priority
     */
    private function getDisplayPriority(): string
    {
        return match ($this->priority) {
            'low' => 'Bassa',
            'normal' => 'Normale',
            'high' => 'Alta',
            'critical' => 'Critica',
            default => 'Normale',
        };
    }
}
