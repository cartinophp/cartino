<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderApproved extends Event
{
    public function __construct($order, ?string $approvedBy = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'approved_by' => $approvedBy,
            'type' => 'order_approved',
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

    public function approvedBy()
    {
        return $this->get('approved_by');
    }
}
