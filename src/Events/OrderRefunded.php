<?php

declare(strict_types=1);

namespace Cartino\Events;

class OrderRefunded extends Event
{
    public function __construct($order, float $amount = 0)
    {
        parent::__construct([
            'order' => $order,
            'entry' => $order,
            'order_number' => $order->get('order_number'),
            'refund_amount' => $amount,
            'type' => 'order_refunded',
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

    public function refundAmount()
    {
        return $this->get('refund_amount');
    }
}
