<?php

declare(strict_types=1);

namespace Cartino\Events\Discounts;

use Cartino\Events\Event;

class DiscountApplied extends Event
{
    public function __construct($discount, $order)
    {
        parent::__construct([
            'discount' => $discount,
            'order' => $order,
            'entry' => $order,
            'code' => $discount->get('code'),
            'order_number' => $order->get('order_number'),
            'type' => 'discount_applied',
            'timestamp' => time(),
        ]);
    }

    public function discount()
    {
        return $this->get('discount');
    }

    public function order()
    {
        return $this->get('order');
    }

    public function code()
    {
        return $this->get('code');
    }
}
