<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderLineRemoved extends Event
{
    public function __construct($order, $line)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'line' => $line,
            'order_number' => $order->get('order_number'),
            'type' => 'order_line_removed',
            'timestamp' => time(),
        ]);
    }

    public function order()
    {
        return $this->get('order');
    }

    public function line()
    {
        return $this->get('line');
    }
}
