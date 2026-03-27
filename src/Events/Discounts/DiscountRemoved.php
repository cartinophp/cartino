<?php

declare(strict_types=1);

namespace Cartino\Events\Discounts;

use Cartino\Events\Event;

class DiscountRemoved extends Event
{
    public function __construct($discount, $order)
    {
        parent::__construct([
            'discount' => $discount,
            'order' => $order,
            'entry' => $order,
            'code' => $discount->get('code'),
            'type' => 'discount_removed',
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
