<?php

declare(strict_types=1);

namespace Cartino\Events;

class OrderPaid extends Event
{
    public function __construct($order)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'total' => $order->get('total'),
            'customer' => $order->get('customer'),
            'type' => 'order_paid',
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

    public function total()
    {
        return $this->get('total');
    }
}
