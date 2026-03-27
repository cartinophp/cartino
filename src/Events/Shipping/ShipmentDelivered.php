<?php

declare(strict_types=1);

namespace Cartino\Events\Shipping;

use Cartino\Events\Event;

class ShipmentDelivered extends Event
{
    public function __construct($order, ?string $trackingNumber = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'tracking_number' => $trackingNumber,
            'type' => 'shipment_delivered',
            'timestamp' => time(),
        ]);
    }

    public function order()
    {
        return $this->get('order');
    }

    public function trackingNumber()
    {
        return $this->get('tracking_number');
    }
}
