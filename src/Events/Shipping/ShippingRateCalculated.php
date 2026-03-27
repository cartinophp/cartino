<?php

declare(strict_types=1);

namespace Cartino\Events\Shipping;

use Cartino\Events\Event;

class ShippingRateCalculated extends Event
{
    public function __construct($cart, $rate, $method = null)
    {
        parent::__construct([
            'cart' => $cart,
            'rate' => $rate,
            'method' => $method,
            'entry' => $cart,
            'type' => 'shipping_rate_calculated',
            'timestamp' => time(),
        ]);
    }

    public function cart()
    {
        return $this->get('cart');
    }

    public function rate()
    {
        return $this->get('rate');
    }

    public function method()
    {
        return $this->get('method');
    }
}
