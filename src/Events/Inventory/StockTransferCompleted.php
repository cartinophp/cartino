<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class StockTransferCompleted extends Event
{
    public function __construct($product, string $fromLocation, string $toLocation, int $quantity)
    {
        parent::__construct([
            'product' => $product,
            'entry' => $product,
            'from_location' => $fromLocation,
            'to_location' => $toLocation,
            'quantity' => $quantity,
            'type' => 'stock_transfer_completed',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function fromLocation()
    {
        return $this->get('from_location');
    }

    public function toLocation()
    {
        return $this->get('to_location');
    }

    public function quantity()
    {
        return $this->get('quantity');
    }
}
