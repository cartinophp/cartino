<?php

declare(strict_types=1);

namespace Cartino\Events;

class OrderStatusChanged extends Event
{
    public function __construct($order, $previousStatus, $newStatus)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'order_number' => $order->get('order_number'),
            'type' => 'order_status_changed',
            'timestamp' => time(),
        ]);
    }

    public function order()
    {
        return $this->get('order');
    }

    public function previousStatus()
    {
        return $this->get('previous_status');
    }

    public function newStatus()
    {
        return $this->get('new_status');
    }

    public function isShipped()
    {
        return $this->newStatus() === 'shipped';
    }

    public function isDelivered()
    {
        return $this->newStatus() === 'delivered';
    }

    public function isCancelled()
    {
        return $this->newStatus() === 'cancelled';
    }

    public function isRefunded()
    {
        return $this->newStatus() === 'refunded';
    }
}
