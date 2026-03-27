<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderApprovalRequired extends Event
{
    public function __construct($order, ?string $reason = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'total' => $order->get('total'),
            'reason' => $reason,
            'type' => 'order_approval_required',
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
