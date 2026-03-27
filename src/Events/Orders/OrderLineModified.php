<?php

declare(strict_types=1);

namespace Cartino\Events\Orders;

use Cartino\Events\Event;

class OrderLineModified extends Event
{
    public function __construct($order, $line, $original = null)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'line' => $line,
            'original' => $original,
            'order_number' => $order->get('order_number'),
            'type' => 'order_line_modified',
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

    public function original()
    {
        return $this->get('original');
    }
}
