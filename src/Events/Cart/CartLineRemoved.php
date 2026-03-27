<?php

declare(strict_types=1);

namespace Cartino\Events\Cart;

use Cartino\Events\Event;

class CartLineRemoved extends Event
{
    public function __construct($cart, $line)
    {
        parent::__construct([
            'cart' => $cart,
            'entry' => $cart,
            'line' => $line,
            'type' => 'cart_line_removed',
            'timestamp' => time(),
        ]);
    }

    public function cart()
    {
        return $this->get('cart');
    }

    public function line()
    {
        return $this->get('line');
    }
}
