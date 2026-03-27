<?php

declare(strict_types=1);

namespace Cartino\Events;

class OrderCancelled extends Event
{
    public function __construct($order, ?string $reason = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'reason' => $reason,
            'type' => 'order_cancelled',
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

    public function reason()
    {
        return $this->get('reason');
    }
}
