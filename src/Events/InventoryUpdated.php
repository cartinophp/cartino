<?php

declare(strict_types=1);

namespace Cartino\Events;

class InventoryUpdated extends Event
{
    public function __construct($product, $previousInventory, $newInventory, $reason = null)
    {
        parent::__construct([
            'product' => $product,
            'previous_inventory' => $previousInventory,
            'new_inventory' => $newInventory,
            'difference' => $newInventory - $previousInventory,
            'reason' => $reason,
            'type' => 'inventory_updated',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function previousInventory()
    {
        return $this->get('previous_inventory');
    }

    public function newInventory()
    {
        return $this->get('new_inventory');
    }

    public function difference()
    {
        return $this->get('difference');
    }

    public function reason()
    {
        return $this->get('reason');
    }

    public function isIncrease()
    {
        return $this->difference() > 0;
    }

    public function isDecrease()
    {
        return $this->difference() < 0;
    }

    public function isLowStock($threshold = 10)
    {
        return $this->newInventory() <= $threshold;
    }

    public function isOutOfStock()
    {
        return $this->newInventory() <= 0;
    }
}
