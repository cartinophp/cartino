<?php

namespace Cartino\Services;

use Cartino\Exceptions\InsufficientStockException;
use Cartino\Models\Order;
use Cartino\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Reduce stock for all products in an order
     */
    public function reduceStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->reduceProductStock($item->product, $item->quantity);
        }

        Log::info('Stock reduced for order', [
            'order_id' => $order->id,
            'items_count' => $order->items->count(),
        ]);
    }

    /**
     * Reduce stock for a specific product
     */
    public function reduceProductStock(Product $product, int $quantity): void
    {
        if ($product->track_quantity && $product->stock_quantity < $quantity) {
            throw new InsufficientStockException(
                "Insufficient stock for product {$product->name}. Available: {$product->stock_quantity}, Required: {$quantity}",
            );
        }

        if ($product->track_quantity) {
            $product->decrement('stock_quantity', $quantity);

            Log::info('Product stock reduced', [
                'product_id' => $product->id,
                'quantity_reduced' => $quantity,
                'remaining_stock' => $product->fresh()->stock_quantity,
            ]);

            // Check for low stock alert
            $this->checkLowStockAlert($product->fresh());
        }
    }

    /**
     * Restore stock (for cancellations, refunds, etc.)
     */
    public function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->restoreProductStock($item->product, $item->quantity);
        }

        Log::info('Stock restored for order', [
            'order_id' => $order->id,
            'items_count' => $order->items->count(),
        ]);
    }

    /**
     * Restore stock for a specific product
     */
    public function restoreProductStock(Product $product, int $quantity): void
    {
        if ($product->track_quantity) {
            $product->increment('stock_quantity', $quantity);

            Log::info('Product stock restored', [
                'product_id' => $product->id,
                'quantity_restored' => $quantity,
                'new_stock' => $product->fresh()->stock_quantity,
            ]);
        }
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasStock(Product $product, int $quantity = 1): bool
    {
        if (! $product->track_quantity) {
            return true;
        }

        return $product->stock_quantity >= $quantity;
    }

    /**
     * Get products with low stock
     */
    public function getLowStockProducts(): Collection
    {
        return Product::where('track_quantity', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->with(['category', 'brand'])
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::where('track_quantity', true)
            ->where('stock_quantity', 0)
            ->with(['category', 'brand'])
            ->get();
    }

    /**
     * Update stock for multiple products
     */
    public function bulkUpdateStock(array $updates): void
    {
        foreach ($updates as $productId => $quantity) {
            $product = Product::findOrFail($productId);

            $product->update([
                'stock_quantity' => $quantity,
                'stock_updated_at' => now(),
            ]);

            Log::info('Stock updated via bulk operation', [
                'product_id' => $productId,
                'new_quantity' => $quantity,
            ]);
        }
    }

    /**
     * Reserve stock (for pending orders)
     */
    public function reserveStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product->track_quantity) {
                $product->increment('reserved_quantity', $item->quantity);

                Log::info('Stock reserved', [
                    'product_id' => $product->id,
                    'quantity_reserved' => $item->quantity,
                    'total_reserved' => $product->fresh()->reserved_quantity,
                ]);
            }
        }
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product->track_quantity) {
                $product->decrement('reserved_quantity', $item->quantity);

                Log::info('Reserved stock released', [
                    'product_id' => $product->id,
                    'quantity_released' => $item->quantity,
                    'remaining_reserved' => $product->fresh()->reserved_quantity,
                ]);
            }
        }
    }

    /**
     * Calculate available stock (total - reserved)
     */
    public function getAvailableStock(Product $product): int
    {
        if (! $product->track_quantity) {
            return PHP_INT_MAX;
        }

        return max(0, $product->stock_quantity - $product->reserved_quantity);
    }

    /**
     * Check for low stock and send alerts
     */
    private function checkLowStockAlert(Product $product): void
    {
        if (
            $product->track_quantity &&
                $product->stock_quantity <= $product->low_stock_threshold &&
                $product->stock_quantity > 0
        ) {
            Log::warning('Low stock alert', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->stock_quantity,
                'threshold' => $product->low_stock_threshold,
            ]);

            // You could dispatch an event here for notifications
            // event(new LowStockAlert($product));
        }

        if ($product->track_quantity && $product->stock_quantity <= 0) {
            Log::warning('Out of stock alert', [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]);

            // You could dispatch an event here for notifications
            // event(new OutOfStockAlert($product));
        }
    }
}
