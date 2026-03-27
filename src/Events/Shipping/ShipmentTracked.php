<?php

declare(strict_types=1);

namespace Cartino\Events\Shipping;

use Cartino\Events\Event;

class ShipmentTracked extends Event
{
    public function __construct($order, string $trackingNumber, ?string $carrier = null, ?string $trackingUrl = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'tracking_url' => $trackingUrl,
            'type' => 'shipment_tracked',
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

    public function carrier()
    {
        return $this->get('carrier');
    }

    public function trackingUrl()
    {
        return $this->get('tracking_url');
    }
}
