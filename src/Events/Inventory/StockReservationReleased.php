<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class StockReservationReleased extends Event
{
    public function __construct($product, $order, int $quantity, ?string $reason = null)
    {
        parent::__construct([
            'product' => $product,
            'order' => $order,
            'entry' => $product,
            'quantity' => $quantity,
            'reason' => $reason,
            'type' => 'stock_reservation_released',
            'timestamp' => time(),
        ]);
    }

    public function product()
    {
        return $this->get('product');
    }

    public function order()
    {
        return $this->get('order');
    }

    public function quantity()
    {
        return $this->get('quantity');
    }

    public function reason()
    {
        return $this->get('reason');
    }
}
