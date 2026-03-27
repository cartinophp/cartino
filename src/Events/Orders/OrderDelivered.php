<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderDelivered extends Event
{
    public function __construct($order)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'type' => 'order_delivered',
            'timestamp' => time(),
        ]);
    }

    public function order()
    {
        return $this->get('order');
    }

    public function orderNumber()
    {
        return $this->get('order_number');
    }
}
