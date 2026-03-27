<?php

declare(strict_types=1);

namespace Cartino\Events\Cart;

use Cartino\Events\Event;

class CartLineUpdated extends Event
{
    public function __construct($cart, $line, $original = null)
    {
        parent::__construct([
            'cart' => $cart,
            'entry' => $cart,
            'line' => $line,
            'original' => $original,
            'type' => 'cart_line_updated',
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

    public function original()
    {
        return $this->get('original');
    }
}
