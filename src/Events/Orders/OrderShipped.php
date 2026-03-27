<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderShipped extends Event
{
    public function __construct($order, ?string $trackingNumber = null, ?string $carrier = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'type' => 'order_shipped',
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

    public function carrier()
    {
        return $this->get('carrier');
    }
}
