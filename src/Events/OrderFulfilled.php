<?php

declare(strict_types=1);

namespace Cartino\Events;

class OrderFulfilled extends Event
{
    public function __construct($order)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'tracking_number' => $order->get('tracking_number'),
            'type' => 'order_fulfilled',
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

    public function trackingNumber()
    {
        return $this->get('tracking_number');
    }
}
