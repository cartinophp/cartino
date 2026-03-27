<?php

declare(strict_types=1);

namespace Cartino\Events\Discounts;

use Cartino\Events\Event;

class DiscountCreated extends Event
{
    public function __construct($discount)
    {
        parent::__construct([
            'discount' => $discount,
            'entry' => $discount,
            'code' => $discount->get('code'),
            'type' => 'discount_created',
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
