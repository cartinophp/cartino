<?php

declare(strict_types=1);

namespace Cartino\Events\Cart;

use Cartino\Events\Event;

class CartAbandoned extends Event
{
    public function __construct($cart)
    {
        parent::__construct([
            'cart' => $cart,
            'entry' => $cart,
            'customer' => $cart->get('customer'),
            'total' => $cart->get('total'),
            'type' => 'cart_abandoned',
            'timestamp' => time(),
        ]);
    }

    public function cart()
    {
        return $this->get('cart');
    }

    public function customer()
    {
        return $this->get('customer');
    }

    public function total()
    {
        return $this->get('total');
    }
}
