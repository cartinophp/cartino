<?php

declare(strict_types=1);

namespace Cartino\Events\Discounts;

use Cartino\Events\Event;

class DiscountUpdated extends Event
{
    public function __construct($discount, $original = null)
    {
        parent::__construct([
            'discount' => $discount,
            'original' => $original,
            'entry' => $discount,
            'code' => $discount->get('code'),
            'type' => 'discount_updated',
            'timestamp' => time(),
        ]);
    }

    public function discount()
    {
        return $this->get('discount');
    }

    public function original()
    {
        return $this->get('original');
    }

    public function code()
    {
        return $this->get('code');
    }
}
