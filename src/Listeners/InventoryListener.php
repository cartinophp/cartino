<?php

namespace Cartino\Listeners;

use Cartino\Events\InventoryUpdated;
use Cartino\Events\OrderCreated;
use Cartino\Events\OrderStatusChanged;
use Cartino\Events\ProductCreated;
use Cartino\Events\ProductUpdated;

class InventoryListener extends Listener
{
    public function handle($eventData, $eventClass = null)
    {
        switch ($eventClass) {
            case InventoryUpdated::class:
                $this->handleInventoryUpdate($eventData);
                break;

            case OrderCreated::class:
                $this->handleOrderCreated($eventData);
                break;

            case OrderStatusChanged::class:
                $this->handleOrderStatusChanged($eventData);
                break;

            case ProductCreated::class:
                $this->handleProductCreated($eventData);
                break;

            case ProductUpdated::class:
                $this->handleProductUpdated($eventData);
                break;
        }
    }

    protected function handleInventoryUpdate($eventData)
    {
        $product = $eventData['product'];
        $newInventory = $eventData['new_inventory'];
        $difference = $eventData['difference'];

        // Log inventory change
        $this->log("Inventory updated for product {$product->id()}", [
            'product_id' => $product->id(),
            'sku' => $product->get('sku'),
            'previous' => $eventData['previous_inventory'],
            'new' => $newInventory,
            'difference' => $difference,
            'reason' => $eventData['reason'],
        ]);

        // Check for low stock alerts
        if ($newInventory <= 10) {
            $this->notify("Low stock alert: {$product->get('title')} has only {$newInventory} items left");
        }

        // Check for out of stock
        if ($newInventory <= 0) {
            $this->notify("Out of stock: {$product->get('title')} is now out of stock");

            // Auto-update product status
            $product->set('status', 'out_of_stock');
            $product->save();
        }

        // Update search index
        $this->updateCache("product_inventory_{$product->id()}", $newInventory);
    }

    protected function handleOrderCreated($eventData)
    {
        $order = $eventData['order'];
        $items = $order->get('items', []);

        // Reduce inventory for ordered items
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            // This would typically fetch the product and update inventory
            $this->log('Reducing inventory for order', [
                'order_number' => $order->get('order_number'),
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            // Queue inventory reduction job
            $this->queue('ReduceInventoryJob', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'reason' => "Order {$order->get('order_number')}",
            ]);
        }
    }

    protected function handleOrderStatusChanged($eventData)
    {
        $order = $eventData['order'];
        $previousStatus = $eventData['previous_status'];
        $newStatus = $eventData['new_status'];

        // If order is cancelled, restore inventory
        if ($newStatus === 'cancelled' && $previousStatus !== 'cancelled') {
            $items = $order->get('items', []);

            foreach ($items as $item) {
                $this->queue('RestoreInventoryJob', [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'reason' => "Order {$order->get('order_number')} cancelled",
                ]);
            }

            $this->log('Queued inventory restoration for cancelled order', [
                'order_number' => $order->get('order_number'),
                'items_count' => count($items),
            ]);
        }
    }

    protected function handleProductCreated($eventData)
    {
        $product = $eventData['product'];
        $initialInventory = $product->get('inventory', 0);

        $this->log('Product created with inventory', [
            'product_id' => $product->id(),
            'sku' => $product->get('sku'),
            'initial_inventory' => $initialInventory,
        ]);

        // Initialize inventory tracking
        $this->updateCache("product_inventory_{$product->id()}", $initialInventory);
    }

    protected function handleProductUpdated($eventData)
    {
        $product = $eventData['product'];
        $original = $eventData['original'];

        if ($original && $product->get('inventory') !== $original->get('inventory')) {
            $this->log('Product inventory updated directly', [
                'product_id' => $product->id(),
                'previous' => $original->get('inventory'),
                'new' => $product->get('inventory'),
            ]);
        }
    }

    public static function subscribe($events = [])
    {
        parent::subscribe([
            InventoryUpdated::class => 10,
            OrderCreated::class => 5,
            OrderStatusChanged::class => 5,
            ProductCreated::class => 0,
            ProductUpdated::class => 0,
        ]);
    }
}
