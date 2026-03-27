<?php

declare(strict_types=1);

namespace Cartino\Events\Inventory;

use Cartino\Events\Event;

class StockReservationCreated extends Event
{
    public function __construct($product, $order, int $quantity)
    {
        parent::__construct([
            'product' => $product,
            'order' => $order,
            'entry' => $product,
            'quantity' => $quantity,
            'type' => 'stock_reservation_created',
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
}
