<?php

declare(strict_types=1);

namespace Cartino\Events\Discounts;

use Cartino\Events\Event;

class DiscountLimitReached extends Event
{
    public function __construct($discount)
    {
        parent::__construct([
            'discount' => $discount,
            'entry' => $discount,
            'code' => $discount->get('code'),
            'type' => 'discount_limit_reached',
            'timestamp' => time(),
        ]);
    }

    public function discount()
    {
        return $this->get('discount');
    }

    public function code()
    {
        return $this->get('code');
    }
}
